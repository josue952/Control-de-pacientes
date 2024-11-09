<template>
    <div class="nav">
        <header-component @toggle-mini-variant="toggleDrawer" />
        <side-bar :is-mini="isMini" @update:isMini="isMini = $event" />
    </div>
    <v-container class="centered-container content-padding">
        <!-- Barra superior con botón Agregar y buscador -->
        <v-row class="d-flex align-center mb-4 mt-4">
            <v-col cols="3">
                <v-btn color="primary" @click="showAddCitaDialog">Agregar Cita</v-btn>
            </v-col>
            <v-col cols="3">
                <v-text-field v-model="search" label="Buscar por paciente" prepend-icon="mdi-magnify"
                            @input="filterCitas"></v-text-field>
            </v-col>
            <v-col cols="3">
                <v-select
                    v-model="filterPagada"
                    :items="['Todos', 'Pagada', 'No pagada']"
                    label="Filtro por pago"
                ></v-select>
            </v-col>
            <v-col cols="3">
                <v-select
                    v-model="filterEstado"
                    :items="['Todos', 'Pendiente', 'Completada', 'Cancelada']"
                    label="Filtro por estado"
                ></v-select>
            </v-col>
        </v-row>

        <!-- Tabla de citas -->
        <v-data-table :headers="tableHeaders" :items="filteredCitas" :items-per-page="10" class="elevation-1 large-table"
            :footer-props="{ 'items-per-page-options': [10, 20, 30] }">
            <template #item="{ item }">
                <tr>
                    <td>{{ item.paciente && item.paciente.usuario ? item.paciente.usuario.nombre_completo : "Sin paciente" }}</td>
                    <td>{{ item.doctor && item.doctor.usuario ? item.doctor.usuario.nombre_completo : "Sin doctor" }}</td>
                    <td>{{ item.fecha_cita }}</td>
                    <td>{{ formatTime12Hour(item.hora_cita) }}</td>
                    <td>{{ item.motivo_consulta }}</td>
                    <td>{{ item.estado }}</td>
                    <td>{{ formatCurrency(item.monto_consulta) }}</td>
                    <td>{{ item.pagada ? "Sí" : "No" }}</td>
                    <td class="action-buttons">
                        <div class="d-flex justify-center">
                            <v-btn size="small" color="primary" class="mx-1" @click="editCita(item)">Editar</v-btn>
                            <v-btn size="small" color="error" class="mx-1" @click="confirmDeleteCita(item)">Eliminar</v-btn>
                        </div>
                    </td>
                </tr>
            </template>
        </v-data-table>

        <!-- Diálogo para agregar/editar cita -->
        <v-dialog v-model="dialog" max-width="900px">
            <v-card>
                <v-card-title>
                    <span class="headline">{{ editMode ? 'Editar Cita' : 'Agregar Cita' }}</span>
                </v-card-title>
                <v-card-text>
                    <v-form ref="form" v-model="valid">
                        <v-select :items="pacientes" item-value="id_paciente" item-title="nombre_completo"
                            label="Seleccione un Paciente" v-model="editedCita.paciente_id" :disabled="editMode" required></v-select>

                        <v-select :items="doctores" item-value="id_doctor" item-title="nombre_completo"
                            label="Seleccione un Doctor" v-model="editedCita.doctor_id" :disabled="editMode" required></v-select>

                        <v-text-field v-model="editedCita.fecha_cita" label="Fecha de la Cita" type="date"
                            required></v-text-field>

                        <!-- Campo de Hora de la Cita en formato de 12 horas -->
                        <v-text-field v-model="editedCita.hora_cita" label="Hora de la Cita" type="time"
                            required></v-text-field>

                        <v-text-field v-model="editedCita.motivo_consulta" label="Motivo de Consulta" type="text"></v-text-field>

                        <v-select :items="['Pendiente', 'Completada', 'Cancelada']" v-model="editedCita.estado"
                            label="Estado" required></v-select>

                        <v-text-field 
                            v-model="editedCita.monto_consulta" 
                            label="Monto de la Consulta" 
                            type="number" 
                            min="0" 
                            required
                            :disabled="!editMode" 
                            :value="editMode ? editedCita.monto_consulta : 0">
                        </v-text-field>

                        <v-switch
                            v-model="editedCita.pagada"
                            label="¿Pagada?"
                            :disabled="!editMode"
                            :color="editedCita.pagada ? 'primary' : 'grey'"
                        />
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="closeDialog">Cancelar</v-btn>
                    <v-btn :disabled="!canSave" color="blue darken-1" text @click="saveCita">Guardar</v-btn>
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
import citasService from "@/services/citasService";
import pacientesService from "@/services/pacientesService";
import doctoresService from "@/services/doctoresService";
import { useRouter } from 'vue-router';

const router = useRouter();
const isMini = ref(true);
const search = ref("");
const dialog = ref(false);
const editMode = ref(false);
const editedCita = ref({ paciente_id: "", doctor_id: "", fecha_cita: "", hora_cita: "", motivo_consulta: "", estado: "Pendiente", monto_consulta: "", pagada: false });
const citaToDelete = ref(null);
const valid = ref(false);
const citas = ref([]);
const pacientes = ref([]);
const doctores = ref([]);
const filterPagada = ref("Todos");
const filterEstado = ref("Todos");


const dynamicDialog = ref(false);
const dialogMessage = ref("");
const dialogType = ref("alert");
const dialogAction = ref(null);

const tableHeaders = [
    { title: 'Paciente', align: 'center', key: 'paciente.nombre_completo', width: '220px' },
    { title: 'Doctor', align: 'center', key: 'doctor.nombre_completo', width: '180px' },
    { title: 'Fecha de la Cita', align: 'center', key: 'fecha_cita', width: '130px' },
    { title: 'Hora de la Cita', align: 'center', key: 'hora_cita', width: '100px' },
    { title: 'Motivo de Consulta', align: 'center', key: 'motivo_consulta', width: '300px' },
    { title: 'Estado', align: 'center', key: 'estado', width: '100px' },
    { title: 'Monto de la Consulta', align: 'center ', key: 'monto_consulta', width: '130px' },
    { title: 'Pagada', align: 'center', key: 'pagada', width: '10px' },
    { title: 'Acciones', align: 'center', key: 'actions', sortable: false, width: '150px' }
];

// Formato de 12 horas
function formatTime12Hour(time) {
    if (!time) return "";
    const [hours, minutes] = time.split(':');
    const hourInt = parseInt(hours, 10);
    const ampm = hourInt >= 12 ? 'PM' : 'AM';
    const formattedHour = hourInt % 12 || 12;
    return `${formattedHour}:${minutes} ${ampm}`;
}

// Función para formatear la moneda
function formatCurrency(value) {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD"
    }).format(value);
}

const canSave = computed(() => {
    return (
        editedCita.value.paciente_id &&
        editedCita.value.doctor_id &&
        editedCita.value.fecha_cita &&
        editedCita.value.hora_cita &&
        editedCita.value.motivo_consulta &&
        editedCita.value.estado !== null &&
        (editMode.value || editedCita.value.monto_consulta === 0) // Monto de consulta en modo creación es 0
    );
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

async function handleDialogAction() {
    if (dialogAction.value) {
        await dialogAction.value();
    }
}

async function getPacientes() {
    const response = await pacientesService.obtenerPacientes();
    // Mapear los pacientes para incluir `nombre_completo`
    pacientes.value = response.map(paciente => ({
        ...paciente,
        nombre_completo: paciente.usuario?.nombre_completo || "Sin nombre"
    }));
    console.log('Pacientes cargados:', pacientes.value);
}

async function getDoctores() {
    const response = await doctoresService.obtenerDoctores();
    // Mapear los doctores para incluir `nombre_completo`
    doctores.value = response.map(doctor => ({
        ...doctor,
        nombre_completo: doctor.usuario?.nombre_completo || "Sin nombre"
    }));
    console.log('Doctores cargados:', doctores.value);
}

async function getCitas() {
    const response = await citasService.obtenerCitas();
    citas.value = response;
    console.log('Citas cargadas:', citas.value);
}

const filteredCitas = computed(() => {
    return citas.value.filter(cita => {
        const nombrePaciente = cita.paciente?.usuario?.nombre_completo || "";

        // Filtro por búsqueda de paciente
        const searchMatch = search.value === "" || nombrePaciente.toLowerCase().includes(search.value.toLowerCase());

        // Filtro por estado de pago
        const pagadaMatch = filterPagada.value === "Todos" ||
            (filterPagada.value === "Pagada" && cita.pagada === 1) ||
            (filterPagada.value === "No pagada" && cita.pagada === 0);

        // Filtro por estado de la consulta
        const estadoMatch = filterEstado.value === "Todos" || cita.estado === filterEstado.value;

        return searchMatch && pagadaMatch && estadoMatch;
    });
});

onMounted(() => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        getCitas();
        getPacientes();
        getDoctores();
    } else {
        router.push({ name: 'Login' });
    }
});

function toggleDrawer() {
    isMini.value = !isMini.value;
}

function showAddCitaDialog() {
    editMode.value = false;
    editedCita.value = {
        paciente_id: "",
        doctor_id: "",
        fecha_cita: "",
        hora_cita: "",
        motivo_consulta: "",
        estado: "Pendiente",
        monto_consulta: 0, // Valor predeterminado de 0
        pagada: false
    };
    dialog.value = true;
}

function editCita(cita) {
    editMode.value = true;
    editedCita.value = {
        id_cita: cita.id_cita,
        paciente_id: cita.paciente_id,
        doctor_id: cita.doctor_id,
        fecha_cita: cita.fecha_cita,
        hora_cita: cita.hora_cita,
        motivo_consulta: cita.motivo_consulta || "", // Asegúrate de que el valor exista o esté vacío
        estado: cita.estado,
        monto_consulta: cita.monto_consulta,
        pagada: cita.pagada === 1 // Asigna `true` si `pagada` es 1, y `false` si es 0
    };
    dialog.value = true;
}

function confirmDeleteCita(cita) {
    citaToDelete.value = cita;
    showDialog(
        `¿Estás seguro de que deseas eliminar la cita de ${cita.paciente.nombre_completo}?`,
        "confirm",
        async () => await deleteCita()
    );
}

async function deleteCita() {
    try {
        await citasService.eliminarCita(citaToDelete.value.id_cita);
        await getCitas();
        citaToDelete.value = null;
    } catch (error) {
        showDialog(error.message || "Error al eliminar la cita", "alert");
    }
}

async function saveCita() {
    try {
        // Asegurar que `hora_cita` tenga el formato "H:i"
        if (editedCita.value.hora_cita) {
            editedCita.value.hora_cita = editedCita.value.hora_cita.slice(0, 5); // "HH:MM"
        }

        if (editMode.value) {
            await citasService.actualizarCita(editedCita.value.id_cita, editedCita.value);
        } else {
            console.log('Cita a guardar:', editedCita.value);
            await citasService.crearCita(editedCita.value);
        }
        closeDialog();
        await getCitas();
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
