<template>
    <v-navigation-drawer v-model="drawer" app dark color="primary" :style="drawerStyle">
        <v-divider></v-divider>

        <v-list dense>
            <v-list-item v-for="item in items" :key="item.title" :to="item.to" link @click="toggleSubMenu(item)">
                <div class="list-item-content">
                    <v-list-item-icon class="icon-with-margin">
                        <v-icon>{{ item.icon }}</v-icon>
                    </v-list-item-icon>
                    <v-list-item-content v-if="!isMini">
                        <v-list-item-title>{{ item.title }}</v-list-item-title>
                    </v-list-item-content>
                    <!-- Flecha condicional en el ítem de "Reportes" -->
                    <v-icon v-if="item.title === 'Reportes'" class="icon-toggle">
                        {{ showReportsSubMenu ? 'mdi-chevron-down' : 'mdi-chevron-up' }}
                    </v-icon>
                </div>
            </v-list-item>

            <!-- Submenú de Reportes -->
            <v-list v-if="showReportsSubMenu" class="sub-menu" dense>
                <v-list-item v-for="subItem in reportItems" :key="subItem.title" :to="subItem.to" link>
                    <div class="list-item-content">
                        <v-list-item-icon class="icon-with-margin">
                            <v-icon>{{ subItem.icon }}</v-icon>
                        </v-list-item-icon>
                        <v-list-item-content v-if="!isMini">
                            <v-list-item-title>{{ subItem.title }}</v-list-item-title>
                        </v-list-item-content>
                    </div>
                </v-list-item>
            </v-list>
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
            drawerWidth: 180,
            miniDrawerWidth: 70,
            showReportsSubMenu: false, // Controla la visibilidad del submenú de reportes
            items: [
                { title: 'Usuarios', icon: 'mdi-account', to: '/usuarios' },
                { title: 'Pacientes', icon: 'mdi-account-group', to: '/pacientes' },
                { title: 'Doctores', icon: 'mdi-doctor', to: '/doctores' },
                { title: 'Citas', icon: 'mdi-calendar-clock', to: '/citas' },
                { title: 'Pagos', icon: 'mdi-cash', to: '/pagos' },
                { title: 'Consultas', icon: 'mdi-stethoscope', to: '/consultas' },
                { title: 'Examenes', icon: 'mdi-test-tube', to: '/examenes' },
                { title: 'Medicamentos', icon: 'mdi-pill', to: '/medicamentos' },
                { title: 'Recetas', icon: 'mdi-file-document', to: '/recetas' },
                { title: 'Reportes', icon: 'mdi-chart-bar', to: null } // Nuevo ítem de "Reportes"
            ],
            reportItems: [ // Subreportes dentro de Reportes
                { title: 'Expedientes', icon: 'mdi-folder-account', to: '/reporte-paciente' },
                { title: 'Recetas', icon: 'mdi-text-box-check-outline', to: '/reporte-receta' },
                { title: 'Examenes', icon: 'mdi-clipboard-pulse-outline', to: '/reporte-examenes' },
                { title: 'Consultas', icon: 'mdi-calendar-range', to: '/reporte-consultas' }
            ]
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
        toggleSubMenu(item) {
            if (item.title === 'Reportes') {
                this.showReportsSubMenu = !this.showReportsSubMenu; // Toggle del submenú de reportes
            }
        }
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
    align-items: center;
}

.icon-with-margin {
    margin-right: 12px;
}

.sub-menu {
    padding-left: 10px; /* Aumenta el margen para indicar que es un submenú */
}
</style>
