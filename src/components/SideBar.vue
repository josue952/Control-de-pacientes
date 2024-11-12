<template>
    <v-navigation-drawer v-if="userRole" v-model="drawer" app dark color="primary" :style="drawerStyle">
        <v-divider></v-divider>

        <v-list dense>
            <v-list-item v-for="item in filteredItems" :key="item.title" :to="item.to" link @click="toggleSubMenu(item)">
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
                <v-list-item v-for="subItem in filteredReportItems" :key="subItem.title" :to="subItem.to" link>
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

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { defineProps } from 'vue';

const drawer = ref(true);
const showReportsSubMenu = ref(false);
const drawerWidth = 180;
const miniDrawerWidth = 70;
const userRole = ref(null);
onMounted(() => {
    //obtener el rol del usuario
    userRole.value = localStorage.getItem('user_role');
    console.log(userRole.value);
});

// Prop recibida desde el componente padre
const props = defineProps({
    isMini: {
        type: Boolean,
        default: false,
    }
});

// Items del sidebar
const items = [
    { title: 'Usuarios', icon: 'mdi-account', to: '/usuarios', roles: ['Administrador', 'Doctor'] },
    { title: 'Pacientes', icon: 'mdi-account-group', to: '/pacientes', roles: ['Administrador', 'Doctor'] },
    { title: 'Doctores', icon: 'mdi-doctor', to: '/doctores', roles: ['Administrador'] },
    { title: 'Citas', icon: 'mdi-calendar-clock', to: '/citas', roles: ['Administrador', 'Doctor', 'Paciente'] },
    { title: 'Pagos', icon: 'mdi-cash', to: '/pagos', roles: ['Administrador', 'Doctor', 'Paciente'] },
    { title: 'Consultas', icon: 'mdi-stethoscope', to: '/consultas', roles: ['Administrador', 'Doctor'] },
    { title: 'Examenes', icon: 'mdi-test-tube', to: '/examenes', roles: ['Administrador', 'Doctor'] },
    { title: 'Medicamentos', icon: 'mdi-pill', to: '/medicamentos', roles: ['Administrador', 'Doctor'] },
    { title: 'Recetas', icon: 'mdi-file-document', to: '/recetas', roles: ['Administrador', 'Doctor'] },
    { title: 'Reportes', icon: 'mdi-chart-bar', to: null, roles: ['Administrador', 'Doctor', 'Paciente'] }
];

// Sub-items de Reportes
const reportItems = [
    { title: 'Expedientes', icon: 'mdi-folder-account', to: '/reporte-paciente', roles: ['Administrador', 'Doctor', 'Paciente'] },
    { title: 'Recetas', icon: 'mdi-text-box-check-outline', to: '/reporte-receta', roles: ['Administrador', 'Doctor', 'Paciente'] },
    { title: 'Examenes', icon: 'mdi-clipboard-pulse-outline', to: '/reporte-examenes', roles: ['Administrador', 'Doctor', 'Paciente'] },
    { title: 'Consultas', icon: 'mdi-calendar-range', to: '/reporte-consultas', roles: ['Administrador', 'Doctor'] }
];

// Computed properties
const drawerStyle = computed(() => ({
    width: props.isMini ? `${miniDrawerWidth}px` : `${drawerWidth}px`,
    transition: 'width 0.3s ease',
}));

const filteredItems = computed(() => items.filter(item => item.roles.includes(userRole.value)));
const filteredReportItems = computed(() => reportItems.filter(item => item.roles.includes(userRole.value)));

// Métodos
function toggleSubMenu(item) {
    if (item.title === 'Reportes') {
        showReportsSubMenu.value = !showReportsSubMenu.value;
    }
}

// Watchers
watch(drawer, (val) => {
    if (!val) {
        drawer.value = true;
    }
});
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
