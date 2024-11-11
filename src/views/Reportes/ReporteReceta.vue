<template>
    <div class="nav">
        <header-component @toggle-mini-variant="toggleDrawer" />
        <side-bar :is-mini="isMini" @update:isMini="isMini = $event" />
    </div>
    <v-container class="centered-container content-padding">
        <!-- Barra superior con selector de paciente, rango de fechas y botón para generar reporte -->
        <v-row class="d-flex align-center mb-4 mt-4">
            <v-col cols="4">
                <v-select
                    v-model="selectedPatient"
                    :items="patientsOptions"
                    label="Seleccionar Paciente para Reporte"
                    item-title="pacienteNombre"
                    item-value="paciente_id"
                    outlined
                ></v-select>
            </v-col>
            <v-col cols="3">
                <v-text-field
                    v-model="fechaDesde"
                    label="Fecha Desde"
                    type="date"
                    outlined
                ></v-text-field>
            </v-col>
            <v-col cols="3">
                <v-text-field
                    v-model="fechaHasta"
                    label="Fecha Hasta"
                    type="date"
                    outlined
                ></v-text-field>
            </v-col>
            <v-col cols="2">
                <v-btn color="primary" @click="generateRecetaReport" :disabled="!selectedPatient || !fechaDesde || !fechaHasta">
                    Generar Reporte
                </v-btn>
            </v-col>
        </v-row>

        <!-- Modal para mostrar el PDF -->
        <v-dialog v-model="showPdfDialog" max-width="1200px">
            <v-card>
                <v-card-title class="headline">Reporte de Recetas</v-card-title>
                <v-card-text>
                    <iframe
                        v-if="pdfUrl"
                        :src="pdfUrl"
                        width="100%"
                        height="750px"
                        frameborder="0"
                    ></iframe>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" @click="showPdfDialog = false">Cerrar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import HeaderComponent from "@/components/HeaderComponent.vue";
import SideBar from "@/components/SideBar.vue";
import pacientesService from "@/services/pacientesService";
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const isMini = ref(true);
const patientsOptions = ref([]);
const selectedPatient = ref(null);
const fechaDesde = ref(null);
const fechaHasta = ref(null);
const pdfUrl = ref(null);
const showPdfDialog = ref(false);

// Cargar pacientes
async function loadPatients() {
    try {
        const response = await pacientesService.obtenerPacientes();
        patientsOptions.value = response.map(paciente => ({
            paciente_id: paciente.id_paciente,
            pacienteNombre: paciente.usuario?.nombre_completo || 'Sin nombre'
        }));
    } catch (error) {
        console.error("Error al cargar pacientes:", error);
    }
}

// Generar reporte de recetas
async function generateRecetaReport() {
    if (!selectedPatient.value || !fechaDesde.value || !fechaHasta.value) {
        alert("Seleccione un paciente y un rango de fechas válido.");
        return;
    }

    try {
        const response = await axios.get(`http://127.0.0.1:8000/api/reportes/recetas/${selectedPatient.value}`, {
            params: {
                fecha_desde: fechaDesde.value,
                fecha_hasta: fechaHasta.value,
            },
            responseType: 'blob'
        });

        pdfUrl.value = window.URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }));
        showPdfDialog.value = true;
    } catch (error) {
        console.error("Error al generar reporte:", error);
        alert("Error al generar el reporte de recetas.");
    }
}

// Cargar pacientes al montar el componente
onMounted(() => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        loadPatients();
    } else {
        router.push({ name: 'Login' });
    }
});

function toggleDrawer() {
    isMini.value = !isMini.value;
}
</script>

<style scoped>
.large-table {
    max-width: 100%;
}

.v-data-table-header th {
    white-space: nowrap;
}

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
</style>
