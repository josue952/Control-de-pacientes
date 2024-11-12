<template>
    <div class="nav">
        <header-component @toggle-mini-variant="toggleDrawer" />
        <side-bar :is-mini="isMini" @update:isMini="isMini = $event" />
    </div>
    <v-container class="centered-container content-padding">
        <!-- Barra superior con selector de paciente y botón para generar reporte -->
        <v-row class="d-flex align-center mb-4 mt-4">
            <v-col cols="8">
                <v-select 
                    v-model="selectedPatient"
                    :items="patientsOptions"
                    label="Seleccionar Paciente para Reporte"
                    item-title="pacienteNombre"
                    item-value="paciente_id"
                    outlined
                    :disabled="isPatientRole"
                ></v-select>
            </v-col>
            <v-col cols="4">
                <v-btn color="primary" @click="generatePatientReport" :disabled="!selectedPatient">
                    Generar Reporte
                </v-btn>
            </v-col>
        </v-row>

        <!-- Modal para mostrar el PDF -->
        <v-dialog v-model="showPdfDialog" max-width="1200px">
            <v-card>
                <v-card-title class="headline">Expediente Médico</v-card-title>
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
const selectedPatient = ref(null); // Almacena el ID del paciente seleccionado
const pdfUrl = ref(null); // URL temporal para el PDF
const showPdfDialog = ref(false); // Controla la visibilidad del modal

// Obtener rol del usuario y usuario actual desde localStorage
const userRole = localStorage.getItem('user_role');
const userId = localStorage.getItem('user_id');
const isPatientRole = userRole === 'Paciente';

// Cargar la lista de pacientes
async function loadPatients() {
    try {
        const response = await pacientesService.obtenerPacientes();
        patientsOptions.value = response.map(paciente => ({
            paciente_id: paciente.id_paciente, 
            pacienteNombre: paciente.usuario?.nombre_completo || 'Sin nombre',
            usuario_id: paciente.usuario_id
        }));

        // Si el usuario es paciente, seleccionar automáticamente su paciente_id
        if (isPatientRole && userId) {
            const paciente = patientsOptions.value.find(p => p.usuario_id === parseInt(userId));
            if (paciente) {
                selectedPatient.value = paciente.paciente_id;
            }
        }
    } catch (error) {
        console.error("Error al cargar pacientes:", error);
    }
}

// Función para generar el reporte del paciente seleccionado
async function generatePatientReport() {
    if (!selectedPatient.value) {
        alert("Seleccione un paciente");
        return;
    }

    try {
        const response = await axios.get(`http://127.0.0.1:8000/api/reportes/expediente/${selectedPatient.value}`, {
            responseType: 'blob' // Recibir el archivo PDF como blob
        });

        // Crear una URL temporal para el blob
        pdfUrl.value = window.URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }));

        // Mostrar el modal con el PDF
        showPdfDialog.value = true;
    } catch (error) {
        console.error("Error al generar reporte:", error);
        alert("Error al generar el reporte del paciente.");
    }
}

// Cargar pacientes al montar el componente
onMounted(() => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        loadPatients(); // Cargar los pacientes cuando se monta el componente
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
