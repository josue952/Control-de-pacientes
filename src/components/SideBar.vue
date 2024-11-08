<template>
    <v-navigation-drawer v-model="drawer" app dark color="primary" :style="drawerStyle">
        <v-divider></v-divider>

        <v-list dense>
            <v-list-item v-for="item in items" :key="item.title" :to="item.to" link>
                <div class="list-item-content">
                    <v-list-item-icon class="icon-with-margin">
                        <v-icon>{{ item.icon }}</v-icon>
                    </v-list-item-icon>
                    <v-list-item-content v-if="!isMini">
                        <v-list-item-title>{{ item.title }}</v-list-item-title>
                    </v-list-item-content>
                </div>
            </v-list-item>
        </v-list>
    </v-navigation-drawer>
</template>

<script>
export default {
    name: 'SideBar',
    props: {
        isMini: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            drawer: true,
            drawerWidth: 190,
            miniDrawerWidth: 70,
            items: [
                { title: 'Dashboard', icon: 'mdi-view-dashboard', to: '/inicio' },
                { title: 'Usuarios', icon: 'mdi-account', to: '/usuarios' },
                { title: 'Pacientes', icon: 'mdi-account-group', to: '/pacientes' },
                { title: 'Doctores', icon: 'mdi-doctor', to: '/doctores' },
                { title: 'Citas', icon: 'mdi-calendar-clock', to: '/citas' },
                { title: 'Historial', icon: 'mdi-history', to: '/historial' },
                { title: 'Configuración', icon: 'mdi-cog', to: '/configuracion' },
            ],
        };
    },
    computed: {
        drawerStyle() {
            return {
                width: this.isMini ? `${this.miniDrawerWidth}px` : `${this.drawerWidth}px`,
                transition: 'width 0.3s ease',
            };
        },
    },
    methods: {
        updateMiniVariant(value) {
            this.$emit('update:isMini', value);
        },
    },
    watch: {
        drawer(val) {
            if (!val) {
                this.drawer = true;
            }
        },
    },
};
</script>

<style scoped>
.list-item-content {
    display: flex;
    align-items: center; /* Alinea el icono y el título en la misma línea */
}

.icon-with-margin {
    margin-right: 12px; /* Espacio entre el ícono y el título */
}
</style>
