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
            userAvatar: null,
            showLogoutDialog: false,
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

                        // Guardar el rol del usuario en localStorage
                        localStorage.setItem('user_role', user.Rol);
                        
                        // Guardar el id del usuario en localStorage
                        localStorage.setItem('user_id', user.id_usuario);
                    }
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

            // Eliminar token, correo electrónico y rol del usuario del localStorage
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user_email');
            localStorage.removeItem('user_role');

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
.headline {
    text-align: center;
}

.large-btn {
    font-size: 18px;
    padding: 10px 16px;
}

.user-info {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-size: 16px;
    font-weight: 500;
}

.user-email {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.7);
}
</style>
