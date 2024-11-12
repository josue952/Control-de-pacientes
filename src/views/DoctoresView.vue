<template>
    <div class="nav">
        <header-component @toggle-mini-variant="toggleDrawer" />
        <side-bar :is-mini="isMini" @update:isMini="isMini = $event" />
    </div>
    <v-container class="centered-container content-padding">
        <!-- Barra superior con botón Agregar, Buscador, y Filtro de Especialidad -->
        <v-row class="d-flex align-center mb-4 mt-4">
            <v-col cols="4">
                <v-btn color="primary" @click="showAddDoctorDialog" :disabled="userRole === 'Paciente'">Agregar Doctor</v-btn>
            </v-col>
            <v-col cols="4">
                <v-text-field v-model="search" label="Buscar por nombre" prepend-icon="mdi-magnify"
                    @input="filterDoctores"></v-text-field>
            </v-col>
            <v-col cols="4">
                <v-select v-model="selectedEspecialidad" :items="especialidadesWithAllOption"
                    label="Filtrar por Especialidad" @change="filterDoctores"></v-select>
            </v-col>
        </v-row>

        <!-- Tabla de doctores -->
        <v-data-table :headers="tableHeaders" :items="filteredDoctores" :items-per-page="10" class="elevation-1"
            :footer-props="{ 'items-per-page-options': [10, 20, 30] }">
            <template #item="{ item }">
                <tr>
                    <td>{{ item.usuario ? item.usuario.nombre_completo : "Sin usuario" }}</td>
                    <td>{{ item.especialidad }}</td>
                    <td>{{ item.telefono }}</td>
                    <td class="action-buttons">
                        <div class="d-flex justify-center">
                            <v-btn size="small" color="primary" class="mx-1" @click="editDoctor(item)" :disabled="userRole === 'Paciente'">
                                Editar
                            </v-btn>
                            <v-btn size="small" color="error" class="mx-1" @click="confirmDeleteDoctor(item)" :disabled="userRole === 'Doctor' || userRole === 'Paciente'">
                                Eliminar
                            </v-btn>
                        </div>
                    </td>
                </tr>
            </template>
        </v-data-table>

        <!-- Diálogo para agregar/editar doctor -->
        <v-dialog v-model="dialog" max-width="500px">
            <v-card>
                <v-card-title>
                    <span class="headline">{{ editMode ? 'Editar Doctor' : 'Agregar Doctor' }}</span>
                </v-card-title>
                <v-card-text>
                    <v-form ref="form" v-model="valid">
                        <!-- Campo de Usuario (Select de Usuarios con Rol Doctor) -->
                        <v-select :items="usuarios" item-value="id_usuario" item-title="nombre_completo"
                            label="Seleccione un Usuario" v-model="editedDoctor.usuario_id" :disabled="editMode"
                            required></v-select>

                        <!-- Campo de Especialidad -->
                        <v-select :items="especialidadesFijas" v-model="editedDoctor.especialidad" label="Especialidad"
                            required>
                        </v-select>
                        <!-- Campo de Teléfono -->
                        <v-text-field v-model="editedDoctor.telefono" label="Teléfono" type="number"></v-text-field>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="closeDialog">Cancelar</v-btn>
                    <v-btn :disabled="!canSave" color="blue darken-1" text @click="saveDoctor">Guardar</v-btn>
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
import doctoresService from "@/services/doctoresService";
import usuariosService from '@/services/usuariosService';
import { useRouter } from 'vue-router';

const router = useRouter();
const isMini = ref(true);
const search = ref("");
const selectedEspecialidad = ref(""); // Controla el filtro de especialidad

// Lista fija de especialidades para creación/edición
const especialidadesFijas = ref([
    "Cardiología",
    "Dermatología",
    "Neurología",
    "Pediatría",
    "Ginecología",
    "Oftalmología",
    "Otorrinolaringología",
    "Psiquiatría",
    "Reumatología",
    "Gastroenterología",
    "Neumología",
    "Endocrinología",
    "Nefrología",
    "Traumatología",
    "Oncología"
]);

// Lista dinámica de especialidades obtenidas para el filtro
const especialidadesFiltro = ref([]); 

const dialog = ref(false);
const editMode = ref(false);
const editedDoctor = ref({});
const doctorToDelete = ref(null);
const valid = ref(false);
const doctores = ref([]);
const usuarios = ref([]);

const dynamicDialog = ref(false);
const dialogMessage = ref("");
const dialogType = ref("alert");
const dialogAction = ref(null);

//obtener el rol del usuario por medio del local storage
const userRole = localStorage.getItem('user_role');

// Especialidades para el filtro incluyendo la opción "Todos"
const especialidadesWithAllOption = computed(() => ["Todos", ...especialidadesFiltro.value]);

const filteredDoctores = computed(() => {
    return doctores.value.filter(doctor => {
        const matchesSearch = search.value === "" || (doctor.usuario && doctor.usuario.nombre_completo.toLowerCase().includes(search.value.toLowerCase()));
        const matchesEspecialidad = selectedEspecialidad.value === "Todos" || selectedEspecialidad.value === "" || doctor.especialidad === selectedEspecialidad.value;
        return matchesSearch && matchesEspecialidad;
    });
});

const tableHeaders = [
    { title: 'Nombre Completo', align: 'start', key: 'usuario.nombre_completo', width: '250px' },
    { title: 'Especialidad', align: 'start', key: 'especialidad', width: '120px' },
    { title: 'Teléfono', align: 'start', key: 'telefono', width: '120px' },
    { title: 'Acciones', align: 'center', key: 'actions', sortable: false, width: '200px' }
];

const canSave = computed(() => {
    return editedDoctor.value.usuario_id && editedDoctor.value.especialidad;
});

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

// Método para cargar especialidades dinámicamente desde la base de datos solo para el filtro
async function getEspecialidadesFiltro() {
    try {
        const doctoresResponse = await doctoresService.obtenerDoctores();
        // Extraer especialidades únicas desde la base de datos
        const especialidadesSet = new Set(doctoresResponse.map(doctor => doctor.especialidad));
        especialidadesFiltro.value = Array.from(especialidadesSet);
    } catch (error) {
        console.error("Error al obtener especialidades para el filtro:", error);
    }
}

// Método para cargar la lista de usuarios filtrada por Rol "Doctor"
async function getUsuarios() {
    try {
        const allUsuariosResponse = await usuariosService.obtenerUsuarios();
        const allUsuariosDoctor = allUsuariosResponse.filter(usuario => usuario.Rol === "Doctor");

        // Obtener todos los doctores registrados
        const allDoctoresResponse = await doctoresService.obtenerDoctores();
        const registeredDoctorIds = new Set(allDoctoresResponse.map(doctor => doctor.usuario_id));

        // Filtrar usuarios con rol "Doctor" que no estén registrados como doctores
        usuarios.value = allUsuariosDoctor
            .filter(usuario => !registeredDoctorIds.has(usuario.id_usuario))
            .map(usuario => ({
                id_usuario: usuario.id_usuario,
                nombre_completo: usuario.nombre_completo,
            }));
    } catch (error) {
        console.error("Error al obtener usuarios o doctores:", error);
    }
}

// Método para obtener todos los doctores
async function getDoctores() {
    try {
        const response = await doctoresService.obtenerDoctores();
        doctores.value = response.map(doctor => ({
            ...doctor,
            especialidad: doctor.especialidad,
            telefono: doctor.telefono,
            usuario: doctor.usuario // Asegúrate de que `usuario` exista en la respuesta
        }));
    } catch (error) {
        console.error("Error al obtener doctores:", error);
    }
}

async function addDoctor(doctor) {
    try {
        const response = await doctoresService.crearDoctor(doctor);
        await getDoctores();
        await getEspecialidadesFiltro(); // Actualiza el filtro de especialidades después de agregar
        return response;
    } catch (error) {
        console.error("Error al crear el doctor:", error);
        showDialog(error.response?.data?.message || error.message || "Error al crear el doctor", "alert");
    }
}

async function updateDoctor(doctor) {
    try {
        const response = await doctoresService.actualizarDoctor(doctor.id_doctor, doctor);
        await getDoctores();
        await getEspecialidadesFiltro(); // Actualiza el filtro de especialidades después de editar
        return response;
    } catch (error) {
        console.error("Error al actualizar el doctor:", error);
        showDialog(error.response?.data?.message || error.message || "Error al actualizar el doctor", "alert");
    }
}

async function deleteDoctor() {
    try {
        await doctoresService.eliminarDoctor(doctorToDelete.value.id_doctor);
        getDoctores();
        doctorToDelete.value = null;
        dynamicDialog.value = false;
    } catch (error) {
        console.error("Error al eliminar el doctor:", error);
        showDialog(error.response?.data?.message || "Ha ocurrido un error al eliminar el doctor", "alert");
    }
}

onMounted(() => {
    getUsuarios();
    getEspecialidadesFiltro(); // Cargar especialidades dinámicas para el filtro
    const token = localStorage.getItem('auth_token');
    if (token) {
        getDoctores();
    } else {
        router.push({ name: 'Login' });
    }
});

function toggleDrawer() {
    isMini.value = !isMini.value;
}

function filterDoctores() { }

function showAddDoctorDialog() {
    editMode.value = false;
    editedDoctor.value = {
        usuario_id: "",
        especialidad: "",
        telefono: ""
    };

    // Recargar usuarios para asegurarse de que solo muestra usuarios no registrados
    getUsuarios();

    dialog.value = true;
}

function editDoctor(doctor) {
    editMode.value = true;
    editedDoctor.value = {
        id_doctor: doctor.id_doctor,
        usuario_id: doctor.usuario.id_usuario,
        especialidad: doctor.especialidad,
        telefono: doctor.telefono
    };

    // Verificar si el usuario ya está en la lista de `usuarios`
    const usuarioExiste = usuarios.value.some(user => user.id_usuario === doctor.usuario.id_usuario);

    // Si no está en la lista, agregarlo temporalmente
    if (!usuarioExiste) {
        usuarios.value.push({
            id_usuario: doctor.usuario.id_usuario,
            nombre_completo: doctor.usuario.nombre_completo
        });
    }

    dialog.value = true;
}

function confirmDeleteDoctor(doctor) {
    doctorToDelete.value = doctor;
    showDialog(
        `¿Estás seguro de que deseas eliminar a ${doctor.usuario.nombre_completo}?`,
        "confirm",
        async () => {
            await deleteDoctor();
        }
    );
}

function closeDialog() {
    dialog.value = false;
}

async function saveDoctor() {
    try {
        if (editMode.value) {
            await updateDoctor(editedDoctor.value);
        } else {
            await addDoctor(editedDoctor.value);
        }
        closeDialog();
    } catch (error) {
        showDialog(error.message, "alert");
    }
}</script>

<style scoped>
.v-row {
    margin-bottom: 16px;
}

.content-padding {
    padding-top: 64px;
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
