<template>
    <v-app-bar app color="primary" dark>
        <v-app-bar-nav-icon @click="$emit('toggle-mini-variant')"></v-app-bar-nav-icon>

        <v-toolbar-title class="flex-grow-1">{{ pageTitle }}</v-toolbar-title>

        <v-spacer></v-spacer>

        <v-menu offset-y max-width="300px">
            <template v-slot:activator="{ on, attrs }">
                <v-btn text v-bind="attrs" v-on="on" class="d-flex align-center large-btn">
                    <v-avatar size="50" class="mr-2">
                        <v-img v-if="userAvatar" :src="userAvatar" alt="User Icon"></v-img>
                        <v-icon v-else>mdi-account</v-icon>
                    </v-avatar>
                    <div class="user-info">
                        <span class="user-name">{{ userName }}</span>
                        <span class="user-email">{{ userEmail }}</span>
                    </div>
                </v-btn>
                <!-- Botón de cierre de sesión -->
                <v-list-item @click="showLogoutDialog = true">
                    <v-list-item-icon>
                        <v-icon>mdi-logout</v-icon>
                    </v-list-item-icon>
                </v-list-item>
            </template>

        </v-menu>

        <!-- Diálogo de confirmación de cierre de sesión -->
        <v-dialog v-model="showLogoutDialog" max-width="280px">
            <v-card>
                <v-card-title class="headline">Confirmar cierre de sesión</v-card-title>
                <v-card-text class="headline">¿Estás seguro de que deseas cerrar sesión?</v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="showLogoutDialog = false">Cancelar</v-btn>
                    <v-btn color="red darken-1" text @click="confirmLogout">Cerrar sesión</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-app-bar>
</template>

<script>
import usuariosService from "@/services/usuariosService";

export default {
    name: 'HeaderComponent',
    data() {
        return {
            userName: '',
            userEmail: '',
            userAvatar: null, // URL para el avatar del usuario si existe
            showLogoutDialog: false // Controla la visibilidad del diálogo de cierre de sesión
        };
    },
    computed: {
        pageTitle() {
            const path = this.$route.path;
            if (path === '/') return 'Dashboard';
            return path.charAt(1).toUpperCase() + path.slice(2);
        }
    },
    methods: {
        async fetchUserData() {
            const email = localStorage.getItem('user_email');
            if (email) {
                try {
                    const user = await usuariosService.obtenerUsuarioPorId(email);
                    if (user) {
                        this.userName = user.nombre_completo;
                        this.userEmail = user.email;
                        this.userAvatar = user.avatar || null;
                    }
                    console.log("Datos del usuario cargados:", user);
                } catch (error) {
                    console.error("Error al obtener los datos del usuario:", error);
                }
            }
        },
        async confirmLogout() {
            this.showLogoutDialog = false;
            try {
                // Hacer la solicitud de cierre de sesión al backend
                await usuariosService.cerrarSesion();
                console.log("Token eliminado del backend.");
            } catch (error) {
                console.error("Error al cerrar sesión en el backend:", error);
            }

            // Eliminar token y correo electrónico del localStorage
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user_email');

            // Redirigir al usuario a la página de inicio de sesión
            this.$router.push({ name: 'Login' });
        }
    },
    mounted() {
        this.fetchUserData(); // Llama a la función para cargar los datos del usuario
    }
};
</script>

<style scoped>
.headline{
    text-align: center;
}

.large-btn {
    font-size: 18px;
    /* Aumenta el tamaño de fuente del botón */
    padding: 10px 16px;
    /* Aumenta el padding del botón */
}

.user-info {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-size: 16px;
    /* Tamaño de fuente para el nombre del usuario */
    font-weight: 500;
}

.user-email {
    font-size: 14px;
    /* Tamaño de fuente para el correo electrónico */
    color: rgba(255, 255, 255, 0.7);
    /* Color más claro para el correo */
}

</style>
