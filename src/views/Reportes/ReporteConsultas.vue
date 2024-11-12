<template>
    <div class="nav">
        <header-component @toggle-mini-variant="toggleDrawer" />
        <side-bar :is-mini="isMini" @update:isMini="isMini = $event" />
    </div>
    <v-container class="centered-container content-padding">
        <!-- Barra superior con selector de doctor y botón para generar reporte -->
        <v-row class="d-flex align-center mb-4 mt-4">
            <v-col cols="8">
                <v-select 
                    v-model="selectedDoctor"
                    :items="doctorsOptions"
                    label="Seleccionar Doctor para Reporte"
                    item-title="doctorNombre"
                    item-value="doctor_id"
                    outlined
                ></v-select>
            </v-col>
            <v-col cols="4">
                <v-btn color="primary" @click="generateDoctorReport" :disabled="!selectedDoctor">
                    Generar Reporte
                </v-btn>
            </v-col>
        </v-row>

        <!-- Modal para mostrar el PDF -->
        <v-dialog v-model="showPdfDialog" max-width="1200px">
            <v-card>
                <v-card-title class="headline">Reporte de Consultas por Doctor</v-card-title>
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
import doctoresService from "@/services/doctoresService"; // Servicio de doctores
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const isMini = ref(true);
const doctorsOptions = ref([]); // Opciones para seleccionar doctores
const selectedDoctor = ref(null); // ID del doctor seleccionado
const pdfUrl = ref(null); // URL temporal para el PDF
const showPdfDialog = ref(false); // Controla la visibilidad del modal

// Cargar la lista de doctores
async function loadDoctors() {
    try {
        const response = await doctoresService.obtenerDoctores(); // Llama al servicio para obtener los doctores
        doctorsOptions.value = response.map(doctor => ({
            doctor_id: doctor.id_doctor, 
            doctorNombre: doctor.usuario?.nombre_completo || 'Sin nombre'
        }));
    } catch (error) {
        console.error("Error al cargar doctores:", error);
    }
}

// Función para generar el reporte del doctor seleccionado
async function generateDoctorReport() {
    if (!selectedDoctor.value) {
        alert("Seleccione un doctor");
        return;
    }

    try {
        const response = await axios.get(`http://127.0.0.1:8000/api/reportes/consultas/doctor/${selectedDoctor.value}`, {
            responseType: 'blob' // Recibir el archivo PDF como blob
        });

        // Crear una URL temporal para el blob
        pdfUrl.value = window.URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }));

        // Mostrar el modal con el PDF
        showPdfDialog.value = true;
    } catch (error) {
        console.error("Error al generar reporte:", error);
        alert("Error al generar el reporte de consultas del doctor.");
    }
}

// Cargar doctores al montar el componente
onMounted(() => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        loadDoctors(); // Cargar los doctores cuando se monta el componente
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
