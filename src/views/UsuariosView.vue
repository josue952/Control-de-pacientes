<template>
    <div class="nav">
        <header-component @toggle-mini-variant="toggleDrawer" />
        <side-bar :is-mini="isMini" @update:isMini="isMini = $event" />
    </div>
    <v-container class="centered-container content-padding">
        <!-- Barra superior con botón Agregar, Buscador y Filtro de Rol -->
        <v-row class="d-flex align-center mb-4 mt-4">
            <v-col cols="4">
                <v-btn color="primary" @click="showAddUserDialog" :disabled="userRole === 'Paciente'">Agregar Usuario</v-btn>
            </v-col>
            <v-col cols="4">
                <v-text-field v-model="search" label="Buscar por nombre" prepend-icon="mdi-magnify"
                    @input="filterUsers"></v-text-field>
            </v-col>
            <!-- Filtro de roles (con "Todos") -->
            <v-col cols="4">
                <v-select v-model="selectedRole" :items="rolesForFilter" label="Filtrar por Rol"
                    @change="filterUsers"></v-select>
            </v-col>
        </v-row>

        <!-- Tabla de usuarios -->
        <v-data-table :headers="tableHeaders" :items="filteredUsers" :items-per-page="10" class="elevation-1"
            :footer-props="{ 'items-per-page-options': [10, 20, 30] }">
            <template #item="{ item }">
                <tr>
                    <!-- Columnas de la tabla, solo se muestran los campos especificados -->
                    <td>{{ item.nombre_completo }}</td>
                    <td>{{ item.email }}</td>
                    <td>{{ item.Rol }}</td>
                    <td>{{ formatDate(item.Fecha_registro) }}</td>
                    <td class="action-buttons">
                        <div class="d-flex justify-center">
                            <v-btn size="small" color="primary" class="mx-1 my-1" @click="editUser(item)" :disabled="userRole === 'Paciente'">
                                Editar
                            </v-btn>
                            <v-btn size="small" color="error" class="mx-1 my-1" @click="confirmDeleteUser(item)" :disabled="userRole === 'Doctor' || userRole === 'Paciente'">
                                Eliminar
                            </v-btn>
                        </div>
                    </td>
                </tr>
            </template>
        </v-data-table>

        <!-- Diálogo para agregar/editar usuario -->
        <v-dialog v-model="dialog" max-width="500px">
            <v-card>
                <v-card-title>
                    <span class="headline">{{ editMode ? 'Editar Usuario' : 'Agregar Usuario' }}</span>
                </v-card-title>
                <v-card-text>
                    <v-form ref="form" v-model="valid">
                        <!-- Campo de Nombre Completo -->
                        <v-text-field v-model="editedUser.nombre_completo" label="Nombre Completo"
                            required></v-text-field>

                        <!-- Campo de Email con validación de tipo -->
                        <v-text-field v-model="editedUser.email" label="Email" type="email" required
                            :rules="[rules.email]"></v-text-field>

                        <!-- Campo de Rol (sin "Todos") -->
                        <v-select v-model="editedUser.Rol" :items="rolesForForm" label="Rol" required
                            :disabled="editMode"></v-select>

                        <!-- Campo de Contraseña con opción de mostrar/ocultar -->
                        <v-text-field v-model="editedUser.password" label="Contraseña"
                            :type="showPassword ? 'text' : 'password'"
                            :append-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                            @click:append="togglePasswordVisibility" :rules="[rules.password]"
                            :required="!editMode"
                            :disabled="userRole === 'Doctor' || userRole === 'Paciente' && editMode"
                            ></v-text-field>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="closeDialog">Cancelar</v-btn>
                    <v-btn :disabled="!canSave" color="blue darken-1" text @click="saveUser">Guardar</v-btn>
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
import usuariosService from "@/services/usuariosService";
import { useRouter } from 'vue-router';

//obtener el rol del usuario por medio del local storage
const userRole = localStorage.getItem('user_role');

const router = useRouter();
const isMini = ref(true);
const search = ref("");
const selectedRole = ref("Todos");
const rolesForFilter = ["Todos", "Administrador", "Paciente", "Doctor"];
const rolesForForm = ["Paciente", "Doctor"]; // Sin "Todos"
const dialog = ref(false);
const editMode = ref(false);
const editedUser = ref({});
const userToDelete = ref(null);
const valid = ref(false);
const users = ref([]);
const form = ref(null);

const dynamicDialog = ref(false);
const dialogMessage = ref("");
const dialogType = ref("alert");
const dialogAction = ref(null);

const filteredUsers = computed(() => {
    return (users.value || [])
        .filter(user => (selectedRole.value === "Todos" || user.Rol === selectedRole.value))
        .filter(user => user.nombre_completo ? user.nombre_completo.toLowerCase().includes(search.value.toLowerCase()) : false);
});

const tableHeaders = [
    { title: 'Nombre Completo', align: 'start', key: 'nombre_completo' },
    { title: 'Correo Electrónico', align: 'start', key: 'email' },
    { title: 'Rol', align: 'start', key: 'Rol' },
    { title: 'Fecha de Registro', align: 'start', key: 'Fecha_registro' },
    { title: 'Acciones', align: 'center', key: 'actions', sortable: false }
];

const rules = {
    email: value => !!value && /.+@.+\..+/.test(value) || 'Introduce un correo válido',
    password: value => !!value && value.length >= 6 || 'La contraseña debe tener al menos 6 caracteres'
};

const canSave = computed(() => {
    const isEmailValid = rules.email(editedUser.value.email) === true;
    const isPasswordValid = editMode.value || rules.password(editedUser.value.password) === true;
    return isEmailValid && isPasswordValid && editedUser.value.nombre_completo && editedUser.value.Rol;
});

// Función para mostrar el diálogo
function showDialog(message, type = "alert", action = null) {
    dialogMessage.value = message;
    dialogType.value = type;
    dialogAction.value = async () => {
        await action();
        dynamicDialog.value = false; // Cierra el diálogo después de la acción
    };
    dynamicDialog.value = true;
}

// Función para manejar la acción en el diálogo
async function handleDialogAction() {
    if (dialogAction.value) {
        await dialogAction.value(); // Ejecuta la acción asignada y cierra el diálogo
    }
}

const showPassword = ref(false);
function togglePasswordVisibility() {
    showPassword.value = !showPassword.value;
}

// Método para obtener todos los usuarios y almacenar sus datos, incluido el id
async function getUsers() {
    try {
        const response = await usuariosService.obtenerUsuarios();
        users.value = response.map(user => ({
            id_usuario: user.id_usuario,
            nombre_completo: user.nombre_completo,
            email: user.email,
            Rol: user.Rol,
            password: user.password,
            Fecha_registro: user.Fecha_registro
        }));
    } catch (error) {
        console.error(error);
    }
}

function formatDate(fecha) {
    const date = new Date(fecha);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

async function addUser(user) {
    try {
        const response = await usuariosService.crearUsuario(user);
        getUsers();
        return response;
    } catch (error) {
        console.error("Error al crear el usuario:", error);
        if (error.response && error.response.data && error.response.data.errors) {
            const errorMessages = Object.values(error.response.data.errors).flat().join(", ");
            throw new Error(errorMessages);
        }
        throw new Error("El usuario ya existe o ha ocurrido un error al crearlo.");
    }
}

async function updateUser(user) {
    try {
        const response = await usuariosService.actualizarUsuario(user.id_usuario, user);
        getUsers();
        return response;
    } catch (error) {
        console.error("Error al actualizar el usuario:", error);
        if (error.response && error.response.data && error.response.data.message) {
            throw new Error(error.response.data.message);
        } else {
            throw new Error("Ha ocurrido un error al actualizar el usuario.");
        }
    }
}

async function deleteUser() {
    try {
        await usuariosService.eliminarUsuario(userToDelete.value.id_usuario);
        getUsers();
        userToDelete.value = null;
        dynamicDialog.value = false; // Asegura que el diálogo se cierre
    } catch (error) {
        console.error("Error al eliminar el usuario:", error);
        showDialog("Ha ocurrido un error al eliminar el usuario.", "alert");
    }
}

onMounted(() => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        getUsers();
    } else {
        router.push({ name: 'Login' });
    }
});

function toggleDrawer() {
    isMini.value = !isMini.value;
}

function filterUsers() { }

function showAddUserDialog() {
    editMode.value = false;
    editedUser.value = {
        nombre_completo: "",
        email: "",
        password: "",
        Rol: "",
        Fecha_registro: new Date().toISOString().slice(0, 10) // Asigna la fecha actual en formato "YYYY-MM-DD"
    };
    dialog.value = true;
}

function editUser(user) {
    editMode.value = true;
    editedUser.value = {
        ...user,
        Fecha_registro: formatDate(user.Fecha_registro)
    };
    dialog.value = true;
}

function confirmDeleteUser(user) {
    userToDelete.value = user;
    showDialog(
        `¿Estás seguro de que deseas eliminar a ${user.nombre_completo}?`,
        "confirm",
        async () => {
            await deleteUser(); // Solo se ejecuta deleteUser una vez que el usuario confirme
        }
    );
}

function closeDialog() {
    dialog.value = false;
}

async function saveUser() {
    try {
        if (editMode.value) {
            await updateUser(editedUser.value);
        } else {
            await addUser(editedUser.value);
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
    /* Altura de la ventana menos la barra de herramientas */
}

.action-buttons {
    text-align: center;
}

.action-buttons .v-btn {
    margin: 0 4px;
}
</style>