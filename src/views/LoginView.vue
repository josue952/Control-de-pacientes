<template>
    <v-container class="fill-height" fluid>
        <v-row align="center" justify="center">
            <v-col cols="12" sm="8" md="6" lg="4">
                <v-card class="elevation-12">
                    <v-toolbar color="primary" dark flat>
                        <v-toolbar-title>Iniciar Sesión</v-toolbar-title>
                    </v-toolbar>
                    <v-card-text>
                        <v-form ref="form" v-model="valid" lazy-validation>
                            <v-text-field v-model="email" :rules="emailRules" label="Correo Electrónico" required
                                prepend-icon="mdi-email"></v-text-field>
                            <v-text-field v-model="password" :rules="passwordRules" label="Contraseña" required
                                prepend-icon="mdi-lock" :type="showPassword ? 'text' : 'password'"
                                :append-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                                @click:append="showPassword = !showPassword"></v-text-field>
                        </v-form>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="primary" :disabled="!valid || password.length < 6" @click="login">
                            Iniciar Sesión
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-col>
        </v-row>

        <!-- Diálogo de error -->
        <v-dialog v-model="showErrorDialog" max-width="400px">
            <v-card>
                <v-card-title class="headline">Error al iniciar sesion</v-card-title>
                <v-card-text>Correo o Contraseña incorrectas</v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="showErrorDialog = false">Cerrar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script>
import axios from 'axios';

export default {
    data: () => ({
        valid: false,
        email: '',
        emailRules: [
            v => !!v || 'El correo electrónico es requerido',
            v => /.+@.+\..+/.test(v) || 'El correo electrónico debe ser válido'
        ],
        password: '',
        passwordRules: [
            v => !!v || 'La contraseña es requerida',
            v => v.length >= 6 || 'La contraseña debe tener al menos 6 caracteres'
        ],
        showPassword: false,
        showErrorDialog: false // Controla la visibilidad del diálogo de error
    }),

    methods: {
        async login() {
            if (this.$refs.form.validate()) {
                try {
                    const response = await axios.post('http://localhost:8000/api/login', {
                        email: this.email,
                        password: this.password
                    });

                    // Guarda el token en el localStorage
                    localStorage.setItem('auth_token', response.data.token);

                    // Guarda el correo electrónico en el localStorage
                    localStorage.setItem('user_email', this.email);

                    // Redirige al usuario al Dashboard
                    this.$router.push({ name: 'Usuarios' });
                } catch (error) {
                    console.error(error);
                    this.showErrorDialog = true; // Muestra el diálogo de error
                }
            }
        }
    }
}
</script>
