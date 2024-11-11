<template>
    <div class="nav">
        <header-component @toggle-mini-variant="toggleDrawer" />
        <side-bar :is-mini="isMini" @update:isMini="isMini = $event" />
    </div>
    <v-container class="centered-container content-padding">
        <!-- Barra superior con botón Agregar y filtros -->
        <v-row class="d-flex align-center mb-4 mt-4">
            <v-col cols="4">
                <v-btn color="primary" @click="showAddConsultaDialog">Agregar Consulta</v-btn>
            </v-col>
            <v-col cols="4">
                <v-select v-model="selectedPaciente" :items="pacientesOptions" label="Filtrar por Paciente"
                    item-title="pacienteNombre" item-value="paciente_id">
                </v-select>
            </v-col>
            <v-col cols="4">
                <v-select v-model="selectedDoctor" :items="doctoresOptions" label="Filtrar por Doctor"
                    item-title="doctorNombre" item-value="doctor_id">
                </v-select>
            </v-col>
        </v-row>

        <!-- Tabla de consultas -->
        <v-data-table :headers="tableHeaders" :items="filteredConsultas" :items-per-page="10"
            class="elevation-1 large-table" :footer-props="{ 'items-per-page-options': [10, 20, 30] }">
            <template #item="{ item }">
                <tr>
                    <td>{{ 'Cita N° ' + item.cita_id }}</td>
                    <td>{{ item.pacienteNombre }}</td> <!-- Nombre del paciente -->
                    <td>{{ item.doctorNombre }}</td> <!-- Nombre del doctor -->
                    <td>{{ item.diagnostico }}</td>
                    <td>{{ item.enfermedad || "No especificado" }}</td>
                    <td>{{ item.observaciones || "No especificado" }}</td>
                    <td>{{ item.tratamiento || "No especificado" }}</td>
                    <td>{{ item.examen_id ? 'Examen N° ' + item.examen_id : "Sin Examenes" }}</td>
                    <!-- Condición para examen -->
                    <td class="action-buttons">
                        <div class="d-flex justify-center">
                            <v-btn size="small" color="primary" class="mx-1" @click="editConsulta(item)">Editar</v-btn>
                            <v-btn size="small" color="error" class="mx-1"
                                @click="confirmDeleteConsulta(item)">Eliminar</v-btn>
                        </div>
                    </td>
                </tr>
            </template>
        </v-data-table>

        <!-- Diálogo para agregar/editar consulta -->
        <v-dialog v-model="dialog" max-width="600px">
            <v-card>
                <v-card-title>
                    <span class="headline">{{ editMode ? 'Editar Consulta' : 'Agregar Consulta' }}</span>
                </v-card-title>
                <v-card-text>
                    <v-form ref="form" v-model="valid">
                        <v-select :items="citas" v-model="editedConsulta.cita_id" label="Cita" required
                            item-title="id_cita" item-value="id_cita" @change="loadPacienteYDoctor"
                            :disabled="editMode">
                            <template #item="{ item }">
                                <div class="d-flex justify-space-between align-center">
                                    <span style="margin-left: 15px;">{{ 'Cita N° ' + item.value }}</span>
                                    <v-icon @click.stop="viewCitaDetails(item.value)"
                                        style="margin-right: 15px;">mdi-eye</v-icon>
                                </div>
                            </template>
                        </v-select>

                        <!-- Select de Paciente -->
                        <v-select :items="citas" v-model="editedConsulta.paciente_id" label="Paciente"
                            item-title="pacienteNombre" item-value="paciente_id" :disabled="true"></v-select>

                        <!-- Select de Doctor -->
                        <v-select :items="citas" v-model="editedConsulta.doctor_id" label="Doctor"
                            item-title="doctorNombre" item-value="doctor_id" :disabled="true"></v-select>

                        <!-- Select de Examen -->
                        <v-select :items="examenes" v-model="editedConsulta.examen_id" label="Examen"
                            item-title="nombre" item-value="id_examen" @change="updateExamenId">
                            <template #item="{ item }">
                                <div class="d-flex justify-space-between align-center">
                                    <span style="margin-left: 15px;">{{ item.value ? `Examen N° ${item.value}` : "Sin Examen" }}</span>
                                    <v-icon @click.stop="viewExamenDetails(item.value)"
                                        style="margin-right: 15px;">mdi-eye</v-icon>
                                </div>
                            </template>
                        </v-select>

                        <v-text-field v-model="editedConsulta.diagnostico" label="Diagnóstico" type="text"
                            required></v-text-field>
                        <v-text-field v-model="editedConsulta.enfermedad" label="Enfermedad" type="text"></v-text-field>
                        <v-textarea v-model="editedConsulta.observaciones" label="Observaciones"></v-textarea>
                        <v-textarea v-model="editedConsulta.tratamiento" label="Tratamiento"></v-textarea>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="closeDialog">Cancelar</v-btn>
                    <v-btn :disabled="!canSave" color="blue darken-1" text @click="saveConsulta">Guardar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Diálogo para ver detalles de la cita -->
        <v-dialog v-model="viewCitaDialog" max-width="500px">
            <v-card>
                <v-card-title>
                    <span class="headline">Detalles de la Cita</span>
                </v-card-title>
                <v-card-text>
                    <p><strong>Paciente:</strong> {{ citaDetails?.paciente?.usuario?.nombre_completo || "N/A" }}</p>
                    <p><strong>Doctor:</strong> {{ citaDetails?.doctor?.usuario?.nombre_completo || "N/A" }}</p>
                    <p><strong>Fecha:</strong> {{ citaDetails?.fecha_cita }}</p>
                    <p><strong>Hora:</strong> {{ citaDetails?.hora_cita }}</p>
                    <p><strong>Motivo:</strong> {{ citaDetails?.motivo_consulta || "N/A" }}</p>
                    <p><strong>Monto:</strong> {{ '$' + citaDetails?.monto_consulta || "0" }}</p>
                    <p><strong>Estado:</strong> {{ citaDetails?.estado }}</p>
                    <p><strong>Pagada:</strong> {{ citaDetails?.pagada === 1 ? "Sí" : "No" }}</p>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="viewCitaDialog = false">Cerrar</v-btn>
                    <v-btn color="green darken-1" text @click="selectCita">Seleccionar Cita</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Diálogo para ver detalles del examen -->
        <v-dialog v-model="viewExamenDialog" max-width="500px">
            <v-card>
                <v-card-title>
                    <span class="headline">Detalles del Examen</span>
                </v-card-title>
                <v-card-text>
                    <p><strong>Nombre:</strong> {{ examenDetails?.nombre || "N/A" }}</p>
                    <p><strong>Descripción:</strong> {{ examenDetails?.descripcion || "N/A" }}</p>
                    <p><strong>Fecha del examen:</strong> {{ examenDetails?.fecha_examen || "N/A" }}</p>
                    <p><strong>Resultados:</strong> {{ examenDetails?.resultados || "N/A" }}</p>
                    <p><strong>Observaciones:</strong> {{ examenDetails?.observaciones || "N/A" }}</p>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="viewExamenDialog = false">Cancelar</v-btn>
                    <v-btn color="green darken-1" text @click="selectExamen">Seleccionar Examen</v-btn>
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
                    <v-btn v-if="dialogType === 'confirm'" color="red darken-1" text
                        @click="handleDialogAction">Aceptar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import HeaderComponent from "@/components/HeaderComponent.vue";
import SideBar from "@/components/SideBar.vue";
import consultasService from "@/services/consultasService";
import citasService from "@/services/citasService";
import examenesService from "@/services/examenesService";
import { useRouter } from 'vue-router';

const router = useRouter();
const isMini = ref(true);
const dialog = ref(false);
const editMode = ref(false);
const editedConsulta = ref({ cita_id: "", paciente_id: "", doctor_id: "", examen_id: "", diagnostico: "", enfermedad: "", observaciones: "", tratamiento: "" });
const consultaToDelete = ref(null);
const valid = ref(false);
const consultas = ref([]);
const citas = ref([]);
const examenes = ref([]); // Lista de exámenes cargados
const viewCitaDialog = ref(false);
const citaDetails = ref(null);
const viewExamenDialog = ref(false); // Controla la visibilidad del diálogo de detalles del examen
const examenDetails = ref(null); // Almacena los detalles del examen seleccionado

// Variables para filtros
const selectedPaciente = ref(null); // Almacena el ID del paciente seleccionado
const selectedDoctor = ref(null); // Almacena el ID del doctor seleccionado
const pacientesOptions = ref([{ paciente_id: null, pacienteNombre: "Todos" }]); // Lista de pacientes únicos con opción "Todos"
const doctoresOptions = ref([{ doctor_id: null, doctorNombre: "Todos" }]); // Lista de doctores únicos con opción "Todos"

const dynamicDialog = ref(false);
const dialogMessage = ref("");
const dialogType = ref("alert");
const dialogAction = ref(null);

const tableHeaders = [
    { title: 'ID de Cita', align: 'start', key: 'cita_id', width: '150px' },
    { title: 'Paciente', align: 'start', key: 'pacienteNombre', width: '150px' },
    { title: 'Doctor', align: 'start', key: 'doctorNombre', width: '150px' },
    { title: 'Diagnóstico', align: 'start', key: 'diagnostico', width: '150px' },
    { title: 'Enfermedad', align: 'start', key: 'enfermedad', width: '150px' },
    { title: 'Observaciones', align: 'start', key: 'observaciones', width: '150px' },
    { title: 'Tratamiento', align: 'start', key: 'tratamiento', width: '150px' },
    { title: 'Examen', align: 'start', key: 'examen_id', width: '150px' },
    { title: 'Acciones', align: 'center', key: 'actions', sortable: false, width: '150px' }
];

// Función para obtener opciones únicas de pacientes y doctores a partir de las consultas
function loadFilterOptions() {
    const pacientesSet = new Set();
    const doctoresSet = new Set();

    consultas.value.forEach(consulta => {
        if (consulta.paciente_id && consulta.pacienteNombre) {
            pacientesSet.add(JSON.stringify({ paciente_id: consulta.paciente_id, pacienteNombre: consulta.pacienteNombre }));
        }
        if (consulta.doctor_id && consulta.doctorNombre) {
            doctoresSet.add(JSON.stringify({ doctor_id: consulta.doctor_id, doctorNombre: consulta.doctorNombre }));
        }
    });

    // Convertir Set en Array para los selects y añadir "Todos"
    pacientesOptions.value = [
        { paciente_id: null, pacienteNombre: "Todos" },
        ...Array.from(pacientesSet).map(item => JSON.parse(item))
    ];
    doctoresOptions.value = [
        { doctor_id: null, doctorNombre: "Todos" },
        ...Array.from(doctoresSet).map(item => JSON.parse(item))
    ];
}

// Función que devuelve las consultas filtradas
const filteredConsultas = computed(() => {
    return consultas.value.filter(consulta => {
        const pacienteMatch = selectedPaciente.value ? consulta.paciente_id === selectedPaciente.value : true;
        const doctorMatch = selectedDoctor.value ? consulta.doctor_id === selectedDoctor.value : true;
        return pacienteMatch && doctorMatch;
    });
});

const canSave = computed(() => {
    const { cita_id, paciente_id, doctor_id, diagnostico, enfermedad, observaciones, tratamiento } = editedConsulta.value;
    return cita_id && paciente_id && doctor_id && diagnostico && enfermedad && observaciones && tratamiento;
});

function showDialog(message = "Ha ocurrido un error", type = "alert", action = null) {
    dialogMessage.value = message;
    dialogType.value = type;
    dialogAction.value = action
        ? async () => {
            await action();
            dynamicDialog.value = false;
        }
        : null;
    dynamicDialog.value = true;
}

// Actualiza loadPacienteYDoctor para cargar nombres del paciente y doctor
function loadPacienteYDoctor() {
    const citaSeleccionada = citas.value.find(cita => cita.id_cita === editedConsulta.value.cita_id);

    if (citaSeleccionada) {
        // Asigna los IDs y nombres del paciente y doctor
        editedConsulta.value.paciente_id = citaSeleccionada.paciente_id;
        editedConsulta.value.doctor_id = citaSeleccionada.doctor_id;
        editedConsulta.value.pacienteNombre = citaSeleccionada.pacienteNombre;
        editedConsulta.value.doctorNombre = citaSeleccionada.doctorNombre;
    } else {
        console.warn("Cita no encontrada en citas:", citas.value);
    }
}

async function handleDialogAction() {
    if (dialogAction.value) {
        await dialogAction.value();
    }
}

// Cargar exámenes desde un servicio
async function getExamenes() {
    try {
        // Cargar los exámenes y las consultas
        const examenesResponse = await examenesService.obtenerExamenes();
        const consultasResponse = await consultasService.obtenerConsultas();

        // Crear un conjunto con los IDs de los exámenes ya utilizados en consultas
        const exámenesUsados = new Set(consultasResponse.map(consulta => consulta.examen_id));

        // Filtrar exámenes: incluir solo los que no están en el conjunto de exámenes usados
        examenes.value = [
            { id_examen: null, nombre: "Sin Examen" }, // Opción por defecto
            ...examenesResponse
                .filter(examen => !exámenesUsados.has(examen.id_examen))
                .map(examen => ({
                    ...examen,
                    nombre: examen.id_examen === null ? "Sin Examen" : `Examen ${examen.id_examen}`
                }))
        ];

        console.log("Exámenes disponibles:", examenes.value);
    } catch (error) {
        console.error("Error al cargar exámenes:", error);
        showDialog("Error inesperado al listar exámenes", "alert");
    }
}

function updateExamenId() {
    // Si se selecciona "Sin Examen" (id_examen es null), asigna 0
    if (editedConsulta.value.examen_id === null) {
        editedConsulta.value.examen_id = 0;
    }
}

// Al seleccionar la cita, actualiza `editedConsulta` con los nombres del paciente y doctor directamente
function selectCita() {
    if (citaDetails.value) {
        console.log("Seleccionando cita:", citaDetails.value);
        editedConsulta.value.cita_id = citaDetails.value.id_cita;

        // Llama a loadPacienteYDoctor para asignar los valores reactivos
        loadPacienteYDoctor();

        viewCitaDialog.value = false; // Cierra el diálogo
    }
}

// Función para seleccionar el examen desde el diálogo de detalles
function selectExamen() {
    if (examenDetails.value) {
        editedConsulta.value.examen_id = examenDetails.value.id_examen;
    }
    viewExamenDialog.value = false;
}

// Obtener citas y consultas al montar el componente
async function getCitas() {
    try {
        // Cargar consultas y filtrar citas ya utilizadas
        const consultasResponse = await consultasService.obtenerConsultas();
        const citasUsadas = new Set(consultasResponse.map(consulta => consulta.cita_id));

        // Cargar citas y filtrar las que no están en citasUsadas
        const citasResponse = await citasService.obtenerCitas();
        citas.value = citasResponse
            .filter(cita => cita.pagada === 1 && !citasUsadas.has(cita.id_cita))
            .map(cita => ({
                ...cita,
                pacienteNombre: cita.paciente?.usuario?.nombre_completo || 'Sin paciente',
                doctorNombre: cita.doctor?.usuario?.nombre_completo || 'Sin doctor'
            }));

        console.log("Citas cargadas (solo pagadas y no utilizadas):", citas.value);
    } catch (error) {
        console.error("Error al cargar citas:", error);
        showDialog("Error inesperado al listar citas", "alert");
    }
}

// Llama a loadFilterOptions cada vez que se carguen las consultas
async function getConsultas() {
    try {
        const response = await consultasService.obtenerConsultas();
        consultas.value = response;
        loadFilterOptions(); // Cargar opciones de filtro después de cargar consultas
        console.log("Consultas cargadas:", consultas.value);
    } catch (error) {
        console.error("Error al cargar consultas:", error);
        showDialog("Error inesperado al listar consultas", "alert");
    }
}

// Mostrar detalles de cita seleccionada
function viewCitaDetails(id) {
    citaDetails.value = citas.value.find(cita => cita.id_cita === id);
    console.log("Detalles de la cita seleccionada:", citaDetails.value);
    viewCitaDialog.value = true;
}

// Función para abrir el diálogo de detalles del examen
function viewExamenDetails(id_examen) {
    examenDetails.value = examenes.value.find(examen => examen.id_examen === id_examen) || null;
    viewExamenDialog.value = true;
}

// Llama a getExamenes al montar el componente
onMounted(() => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        getConsultas();
        getCitas();
        getExamenes();
    } else {
        router.push({ name: 'Login' });
    }
});

function toggleDrawer() {
    isMini.value = !isMini.value;
}

function showAddConsultaDialog() {
    editMode.value = false;
    editedConsulta.value = {
        cita_id: "",
        paciente_id: "",
        doctor_id: "",
        examen_id: null, // Inicializa en null para seleccionar "Sin Examen" por defecto
        diagnostico: "",
        enfermedad: "",
        observaciones: "",
        tratamiento: ""
    };
    dialog.value = true;
}

function editConsulta(consulta) {
    editMode.value = true;
    editedConsulta.value = { ...consulta };
    loadPacienteYDoctor(); // Cargar nombres al editar
    dialog.value = true;
}

function confirmDeleteConsulta(consulta) {
    consultaToDelete.value = consulta;
    showDialog(
        `¿Estás seguro de que deseas eliminar la consulta de la cita N° ${consulta.cita_id}?`,
        "confirm",
        async () => await deleteConsulta()
    );
}

async function deleteConsulta() {
    try {
        await consultasService.eliminarConsulta(consultaToDelete.value.id_consulta);
        getExamenes();
        await getConsultas();
        consultaToDelete.value = null;
    } catch (error) {
        showDialog(error.message || "Error al eliminar la consulta", "alert");
    }
}

async function saveConsulta() {
    try {
        if (editMode.value) {
            await consultasService.actualizarConsulta(editedConsulta.value.id_consulta, editedConsulta.value);
        } else {
            await consultasService.crearConsulta(editedConsulta.value);
        }
        closeDialog();
        getExamenes(); // Actualizar lista de exámenes
        await getConsultas();
    } catch (error) {
        showDialog(error, "alert");
    }
}

function closeDialog() {
    dialog.value = false;
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
