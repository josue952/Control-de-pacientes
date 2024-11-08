<template>
    <div class="nav">
        <header-component @toggle-mini-variant="toggleDrawer" />
        <side-bar :is-mini="isMini" @update:isMini="isMini = $event" />
    </div>
    <v-container class="centered-container content-padding">
        <!-- Barra superior con botón Agregar, Buscador, y Filtro de Género -->
        <v-row class="d-flex align-center mb-4 mt-4">
            <v-col cols="4">
                <v-btn color="primary" @click="showAddPacienteDialog">Agregar Paciente</v-btn>
            </v-col>
            <v-col cols="4">
                <v-text-field v-model="search" label="Buscar por nombre" prepend-icon="mdi-magnify"
                    @input="filterPacientes"></v-text-field>
            </v-col>
            <v-col cols="4">
                <v-select v-model="selectedGenero" :items="['Todos', 'Masculino', 'Femenino']"
                    label="Filtrar por Género" @change="filterPacientes"></v-select>
            </v-col>
        </v-row>

        <!-- Tabla de pacientes -->
        <v-data-table :headers="tableHeaders" :items="filteredPacientes" :items-per-page="10" class="elevation-1"
            :footer-props="{ 'items-per-page-options': [10, 20, 30] }">
            <template #item="{ item }">
                <tr>
                    <td>{{ item.usuario ? item.usuario.nombre_completo : "Sin usuario" }}</td>
                    <td>{{ formatDate(item.fecha_nacimiento) }}</td>
                    <td>{{ item.genero }}</td>
                    <td>{{ item.edad }}</td>
                    <td>{{ item.direccion }}</td>
                    <td>{{ item.telefono }}</td>
                    <td>{{ formatDate(item.fecha_registro) }}</td>
                    <td class="action-buttons">
                        <div class="d-flex justify-center">
                            <v-btn size="small" color="primary" class="mx-1" @click="editPaciente(item)">
                                Editar
                            </v-btn>
                            <v-btn size="small" color="error" class="mx-1" @click="confirmDeletePaciente(item)">
                                Eliminar
                            </v-btn>
                        </div>
                    </td>
                </tr>
            </template>
        </v-data-table>

        <!-- Diálogo para agregar/editar paciente -->
        <v-dialog v-model="dialog" max-width="500px">
            <v-card>
                <v-card-title>
                    <span class="headline">{{ editMode ? 'Editar Paciente' : 'Agregar Paciente' }}</span>
                </v-card-title>
                <v-card-text>
                    <v-form ref="form" v-model="valid">
                        <!-- Campo de Usuario (Select de Usuarios con Rol Paciente) -->
                        <v-select :items="usuarios" item-value="id_usuario" item-title="nombre_completo"
                            label="Seleccione un Usuario" v-model="editedPaciente.usuario_id" required></v-select>

                        <!-- Campo de Fecha de Nacimiento con evento de entrada -->
                        <v-text-field v-model="editedPaciente.fecha_nacimiento" label="Fecha de Nacimiento" type="date"
                            required @input="editedPaciente.edad = calculateAge(editedPaciente.fecha_nacimiento)">
                        </v-text-field>

                        <!-- Campo de Género -->
                        <v-select v-model="editedPaciente.genero" :items="generos" label="Género" required></v-select>

                        <!-- Campo de Edad deshabilitado -->
                        <v-text-field v-model="editedPaciente.edad" label="Edad" type="number" disabled></v-text-field>

                        <!-- Campo de Dirección -->
                        <v-text-field v-model="editedPaciente.direccion" label="Dirección" type="text"></v-text-field>

                        <!-- Campo de Teléfono -->
                        <v-text-field v-model="editedPaciente.telefono" label="Teléfono" type="text"></v-text-field>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="closeDialog">Cancelar</v-btn>
                    <v-btn :disabled="!canSave" color="blue darken-1" text @click="savePaciente">Guardar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Diálogo dinámico para confirmación de eliminación -->
        <v-dialog v-model="dynamicDialog" max-width="400px">
            <v-card>
                <v-card-title class="headline">{{ dialogType === 'alert' ? 'Alerta' : 'Confirmación' }}</v-card-title>
                <v-card-text>{{ dialogMessage }}</v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="dynamicDialog = false">Cerrar</v-btn>
                    <v-btn v-if="dialogType === 'confirm'" color="red darken-1" text @click="handleDialogAction">
                        Aceptar
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import HeaderComponent from "@/components/HeaderComponent.vue";
import SideBar from "@/components/SideBar.vue";
import pacientesService from "@/services/pacientesService";
import usuariosService from '@/services/usuariosService';
import { useRouter } from 'vue-router';

const router = useRouter();
const isMini = ref(true);
const search = ref("");
const selectedGenero = ref("Todos"); // Controla el filtro de género
const generos = ["Masculino", "Femenino"];
const dialog = ref(false);
const editMode = ref(false);
const editedPaciente = ref({});
const pacienteToDelete = ref(null);
const valid = ref(false);
const pacientes = ref([]);
const usuarios = ref([]);

const dynamicDialog = ref(false);
const dialogMessage = ref("");
const dialogType = ref("alert");
const dialogAction = ref(null);

const filteredPacientes = computed(() => {
    return (pacientes.value || [])
        .filter(paciente => {
            const matchesSearch = paciente.usuario?.nombre_completo
                ? paciente.usuario.nombre_completo.toLowerCase().includes(search.value.toLowerCase())
                : false;
            const matchesGenero = selectedGenero.value === "Todos" || paciente.genero === selectedGenero.value;
            return matchesSearch && matchesGenero;
        });
});

const tableHeaders = [
    { title: 'Nombre Completo', align: 'start', key: 'usuario.nombre_completo', width: '25  0px' },
    { title: 'Fecha de Nacimiento', align: 'start', key: 'fecha_nacimiento', width: '120px' },
    { title: 'Género', align: 'start', key: 'genero' },
    { title: 'Edad', align: 'start', key: 'edad' },
    { title: 'Dirección', align: 'start', key: 'direccion' },
    { title: 'Teléfono', align: 'start', key: 'telefono' },
    { title: 'Fecha de Registro', align: 'start', key: 'fecha_registro', width: '120px' },
    { title: 'Acciones', align: 'center', key: 'actions', sortable: false, width: '200px' }
];

const canSave = computed(() => {
    return (
        editedPaciente.value.usuario_id &&
        editedPaciente.value.fecha_nacimiento &&
        editedPaciente.value.genero &&
        editedPaciente.value.edad
    );
});

function calculateAge(birthDate) {
    if (!birthDate) return null; // Evita errores si birthDate es undefined
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    return age;
}

// Función para mostrar el diálogo
function showDialog(message = "Ha ocurrido un error", type = "alert", action = null) {
    dialogMessage.value = message || "Ha ocurrido un error desconocido";
    dialogType.value = type;
    dialogAction.value = action
        ? async () => {
            await action();
            dynamicDialog.value = false;
        }
        : null;
    dynamicDialog.value = true;
}

async function handleDialogAction() {
    if (dialogAction.value) {
        await dialogAction.value();
    }
}

// Método para cargar la lista de usuarios filtrada por Rol "Paciente" que no están registrados como pacientes
async function getUsuarios() {
    try {
        // Obtener todos los usuarios con rol "Paciente"
        const allUsuariosResponse = await usuariosService.obtenerUsuarios();
        const allUsuariosPaciente = allUsuariosResponse.filter(usuario => usuario.Rol === "Paciente");

        // Obtener todos los pacientes registrados
        const allPacientesResponse = await pacientesService.obtenerPacientes();
        const pacienteIds = new Set(allPacientesResponse.map(paciente => paciente.usuario_id));

        // Filtrar usuarios para excluir los que ya son pacientes y asignarlos a `usuarios`
        usuarios.value = allUsuariosPaciente
            .filter(usuario => !pacienteIds.has(usuario.id_usuario))
            .map(usuario => ({
                id_usuario: usuario.id_usuario,
                nombre_completo: usuario.nombre_completo,
            }));
    } catch (error) {
        console.error("Error al obtener usuarios o pacientes:", error);
    }
}

// Método para obtener todos los pacientes
async function getPacientes() {
    try {
        const response = await pacientesService.obtenerPacientes();
        pacientes.value = response.map(paciente => ({
            ...paciente,
            fecha_nacimiento: paciente.fecha_nacimiento,
            fecha_registro: paciente.fecha_registro
        }));
    } catch (error) {
        console.error(error);
    }
}

function formatDate(fecha) {
    const date = new Date(fecha);
    // Extraer componentes sin ajustar la hora
    const year = date.getUTCFullYear();
    const month = String(date.getUTCMonth() + 1).padStart(2, '0');
    const day = String(date.getUTCDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

async function addPaciente(paciente) {
    try {
        const response = await pacientesService.crearPaciente(paciente);
        getPacientes();
        return response;
    } catch (error) {
        console.error("Error al crear el paciente:", error);
        const errorMsg = error.response?.data?.message || error.message || "Error al crear el paciente.";
        showDialog(errorMsg, "alert");
    }
}

async function updatePaciente(paciente) {
    try {
        const response = await pacientesService.actualizarPaciente(paciente.id_paciente, paciente);
        getPacientes();
        return response;
    } catch (error) {
        console.error("Error al actualizar el paciente:", error);
        const errorMsg = error.response?.data?.message || error.message || "Error al actualizar el paciente.";
        showDialog(errorMsg, "alert");
    }
}

async function deletePaciente() {
    try {
        await pacientesService.eliminarPaciente(pacienteToDelete.value.id_paciente);
        getPacientes();
        pacienteToDelete.value = null;
        dynamicDialog.value = false;
    } catch (error) {
        console.error("Error al eliminar el paciente:", error);
        const errorMsg = error.response?.data?.message || "Ha ocurrido un error al eliminar el paciente.";
        showDialog(errorMsg, "alert");
    }
}

onMounted(() => {
    getUsuarios(); // Asegura que se carguen los usuarios al montar el componente
    const token = localStorage.getItem('auth_token');
    if (token) {
        getPacientes();
    } else {
        router.push({ name: 'Login' });
    }
});

function toggleDrawer() {
    isMini.value = !isMini.value;
}

function filterPacientes() { }

function showAddPacienteDialog() {
    editMode.value = false;
    editedPaciente.value = {
        usuario_id: "",
        fecha_nacimiento: "",
        genero: "",
        edad: "",
        direccion: "",
        telefono: "",
    };
    dialog.value = true;
}

function editPaciente(paciente) {
    editMode.value = true;
    editedPaciente.value = {
        id_paciente: paciente.id_paciente,
        usuario_id: paciente.usuario.id_usuario,
        fecha_nacimiento: paciente.fecha_nacimiento, // Usa directamente el formato original
        genero: paciente.genero,
        edad: calculateAge(paciente.fecha_nacimiento), // Calcula la edad al cargar
        direccion: paciente.direccion,
        telefono: paciente.telefono,
    };
    // Añadir el usuario a la lista si falta
    if (!usuarios.value.some(user => user.id_usuario === paciente.usuario.id_usuario)) {
        usuarios.value.push({
            id_usuario: paciente.usuario.id_usuario,
            nombre_completo: paciente.usuario.nombre_completo,
        });
    }
    dialog.value = true;
}

function confirmDeletePaciente(paciente) {
    pacienteToDelete.value = paciente;
    showDialog(
        `¿Estás seguro de que deseas eliminar a ${paciente.usuario.nombre_completo}?`,
        "confirm",
        async () => {
            await deletePaciente();
        }
    );
}

function closeDialog() {
    dialog.value = false;
}

async function savePaciente() {
    try {
        if (editMode.value) {
            await updatePaciente(editedPaciente.value); // Aquí `editedPaciente` ahora tiene `id_paciente`
        } else {
            await addPaciente(editedPaciente.value);
        }
        closeDialog();
    } catch (error) {
        showDialog(error.message, "alert");
    }
}
</script>

<style scoped>
.v-row {
    margin-bottom: 16px;
}

.content-padding {
    padding-top: 64px;
    /* Altura estándar de v-app-bar */
}

.centered-container {
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 64px);
}

.action-buttons {
    text-align: center;
}

.action-buttons .v-btn {
    margin: 0 4px;
}
</style>
