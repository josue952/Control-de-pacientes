<template>
    <div class="nav">
        <header-component @toggle-mini-variant="toggleDrawer" />
        <side-bar :is-mini="isMini" @update:isMini="isMini = $event" />
    </div>
    <v-container class="centered-container content-padding">
        <!-- Barra superior con botón Agregar -->
        <v-row class="d-flex align-center mb-4 mt-4">
            <v-col cols="2">
                <v-btn color="primary" @click="showAddRecetaDialog">Agregar Receta</v-btn>
            </v-col>
        </v-row>

        <!-- Tabla de recetas -->
        <v-data-table :headers="tableHeaders" :items="filteredRecetas" :items-per-page="10"
            class="elevation-1 large-table" :footer-props="{ 'items-per-page-options': [10, 20, 30] }">
            <template #item="{ item }">
                <tr>
                    <td>{{ 'Consulta N° ' + item.consulta_id }}</td>
                    <td>{{ item.medicamento?.nombre || 'No especificado' }}</td>
                    <td>{{ item.cantidad }}</td>
                    <td>{{ item.dosis_prescrita || "No especificado" }}</td>
                    <td>{{ item.duracion || "No especificado" }}</td>
                    <td class="action-buttons">
                        <div class="d-flex justify-center">
                            <v-btn size="small" color="primary" class="mx-1" @click="editReceta(item)">Editar</v-btn>
                            <v-btn size="small" color="error" class="mx-1" @click="confirmDeleteReceta(item)" :disabled="userRole === 'Paciente'">Eliminar</v-btn>
                        </div>
                    </td>
                </tr>
            </template>
        </v-data-table>

        <!-- Diálogo para agregar/editar receta -->
        <v-dialog v-model="dialog" max-width="600px">
            <v-card>
                <v-card-title>
                    <span class="headline">{{ editMode ? 'Editar Receta' : 'Agregar Receta' }}</span>
                </v-card-title>
                <v-card-text>
                    <v-form ref="form" v-model="valid">
                        <v-select :items="consultas" v-model="editedReceta.consulta_id" label="Consulta" required item-title="id_consulta"
                            item-value="id_consulta">
                            <template #item="{ item }">
                                <div class="d-flex justify-space-between align-center">
                                    <span style="margin-left: 15px;">{{ 'Consulta N° ' + item.value }}</span>
                                    <v-icon @click.stop="viewConsultaDetails(item.value)" style="margin-right: 15px;">mdi-eye</v-icon>
                                </div>
                            </template>
                        </v-select>

                        <v-select :items="medicamentos" v-model="editedReceta.medicamento_id" label="Medicamento" required item-title="nombre" item-value="id_medicamento"></v-select>
                        <v-text-field v-model="editedReceta.cantidad" label="Cantidad" type="number" min="1" required></v-text-field>
                        <v-text-field v-model="editedReceta.dosis_prescrita" label="Dosis Prescrita" type="text"></v-text-field>
                        <v-text-field v-model="editedReceta.duracion" label="Duración" type="text"></v-text-field>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="closeDialog">Cancelar</v-btn>
                    <v-btn :disabled="!canSave" color="blue darken-1" text @click="saveReceta">Guardar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Diálogo para ver detalles de la consulta -->
        <v-dialog v-model="viewConsultaDialog" max-width="500px">
            <v-card>
                <v-card-title>
                    <span class="headline">Detalles de la Consulta</span>
                </v-card-title>
                <v-card-text>
                    <p><strong>Consulta:</strong> {{ 'Consulta N° ' + consultaDetails?.id_consulta }}</p>
                    <p><strong>Cita:</strong> {{ 'Cita N° ' + consultaDetails?.cita_id }}</p>
                    <p><strong>Paciente:</strong> {{ consultaDetails?.pacienteNombre }}</p>
                    <p><strong>Doctor:</strong> {{ consultaDetails?.doctorNombre }}</p>
                    <p><strong>Examen:</strong> {{ 'Examen N° ' + consultaDetails?.examen_id }}</p>
                    <p><strong>Diagnóstico:</strong> {{ consultaDetails?.diagnostico }}</p>
                    <p><strong>Enfermedad:</strong> {{ consultaDetails?.enfermedad }}</p>
                    <p><strong>Observaciones:</strong> {{ consultaDetails?.observaciones }}</p>
                    <p><strong>Tratamiento:</strong> {{ consultaDetails?.tratamiento }}</p>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="viewConsultaDialog = false">Cerrar</v-btn>
                    <v-btn color="green darken-1" text @click="selectConsulta">Seleccionar Consulta</v-btn>
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
                    <v-btn v-if="dialogType === 'confirm'" color="red darken-1" text @click="handleDialogAction">Aceptar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import HeaderComponent from "@/components/HeaderComponent.vue";
import SideBar from "@/components/SideBar.vue";
import recetasService from "@/services/recetasService";
import medicamentosService from "@/services/medicamentosService";
import consultasService from "@/services/consultasService"; // Asegúrate de que esté apuntando al controlador correcto en el backend
import { useRouter } from 'vue-router';

const router = useRouter();
const isMini = ref(true);
const dialog = ref(false);
const editMode = ref(false);
const editedReceta = ref({ consulta_id: null, medicamento_id: null, cantidad: "", dosis_prescrita: "", duracion: "" });
const recetaToDelete = ref(null);
const valid = ref(false);
const recetas = ref([]);
const medicamentos = ref([]);
const consultas = ref([]);
const viewConsultaDialog = ref(false);
const consultaDetails = ref(null);

const dynamicDialog = ref(false);
const dialogMessage = ref("");
const dialogType = ref("alert");
const dialogAction = ref(null);

// Obtener rol del usuario y paciente actual de localStorage
const userRole = localStorage.getItem('user_role');

const tableHeaders = [
    { title: 'ID de Consulta', align: 'start', key: 'consulta_id', width: '200px' },
    { title: 'Medicamento', align: 'start', key: 'medicamento', width: '200px' },
    { title: 'Cantidad', align: 'start', key: 'cantidad', width: '50px' },
    { title: 'Dosis Prescrita', align: 'start', key: 'dosis_prescrita', width: '150px' },
    { title: 'Duración', align: 'start', key: 'duracion', width: '150px' },
    { title: 'Acciones', align: 'center', key: 'actions', sortable: false, width: '150px' }
];

const filteredRecetas = computed(() => recetas.value);
const canSave = computed(() => editedReceta.value.consulta_id && editedReceta.value.medicamento_id && editedReceta.value.cantidad);

onMounted(async () => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        await getRecetas();
        await getMedicamentos();
        await getConsultas();
    } else {
        router.push({ name: 'Login' });
    }
});

async function getRecetas() {
    try {
        const response = await recetasService.obtenerRecetas();
        recetas.value = response;
    } catch (error) {
        showDialog(error.message || "Error al cargar recetas", "alert");
    }
}

async function getMedicamentos() {
    try {
        const response = await medicamentosService.obtenerMedicamentos();
        medicamentos.value = response;
    } catch (error) {
        showDialog(error.message || "Error al cargar medicamentos", "alert");
    }
}

async function getConsultas() {
    try {
        const consultasUsadas = new Set(recetas.value.map(receta => receta.consulta_id));
        const response = await consultasService.obtenerConsultas();
        
        consultas.value = response
            .filter(consulta => !consultasUsadas.has(consulta.id_consulta))
            .map(consulta => ({
                id_consulta: consulta.id_consulta,
                cita_id: consulta.cita_id,
                paciente_id: consulta.paciente_id,
                doctor_id: consulta.doctor_id,
                pacienteNombre: consulta.pacienteNombre,
                doctorNombre: consulta.doctorNombre,
                examen_id: consulta.examen_id,
                diagnostico: consulta.diagnostico,
                enfermedad: consulta.enfermedad,
                observaciones: consulta.observaciones,
                tratamiento: consulta.tratamiento,
            }));
    } catch (error) {
        showDialog(error.message || "Error al cargar consultas", "alert");
    }
    console.log('Consultas:', consultas.value);
}

function viewConsultaDetails(id) {
    consultaDetails.value = consultas.value.find(consulta => consulta.id_consulta === id);
    viewConsultaDialog.value = true;
}

function selectConsulta() {
    if (consultaDetails.value) {
        editedReceta.value.consulta_id = consultaDetails.value.id_consulta;
    }
    viewConsultaDialog.value = false;
}

function showDialog(message, type = "alert", action = null) {
    dialogMessage.value = message;
    dialogType.value = type;
    dialogAction.value = action ? async () => { await action(); dynamicDialog.value = false; } : null;
    dynamicDialog.value = true;
}

function showAddRecetaDialog() {
    editMode.value = false;
    editedReceta.value = { consulta_id: null, medicamento_id: null, cantidad: "", dosis_prescrita: "", duracion: "" };
    dialog.value = true;
}

function editReceta(receta) {
    editMode.value = true;
    editedReceta.value = { ...receta };
    dialog.value = true;
}

function confirmDeleteReceta(receta) {
    recetaToDelete.value = receta;
    showDialog(
        `¿Estás seguro de que deseas eliminar la receta de la consulta N° ${receta.consulta_id}?`,
        "confirm",
        deleteReceta
    );
}

async function handleDialogAction() {
    if (dialogAction.value) {
        await dialogAction.value();
        dynamicDialog.value = false;
    }
}

async function deleteReceta() {
    try {
        await recetasService.eliminarReceta(recetaToDelete.value.id_receta);
        await getRecetas();
        recetaToDelete.value = null;
    } catch (error) {
        showDialog(error.message || "Error al eliminar la receta", "alert");
    }
}

async function saveReceta() {
    try {
        if (editMode.value) {
            await recetasService.actualizarReceta(editedReceta.value.id_receta, editedReceta.value);
        } else {
            await recetasService.crearReceta(editedReceta.value);
        }
        closeDialog();
        await getRecetas();
    } catch (error) {
        showDialog(error, "alert");
    }
}

function closeDialog() {
    dialog.value = false;
}

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

.action-buttons {
    text-align: center;
}

.action-buttons .v-btn {
    margin: 0 4px;
}
</style>
