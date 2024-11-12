<template>
    <div class="nav">
        <header-component @toggle-mini-variant="toggleDrawer" />
        <side-bar :is-mini="isMini" @update:isMini="isMini = $event" />
    </div>
    <v-container class="centered-container content-padding">
        <!-- Barra superior con botón Agregar y filtros -->
        <v-row class="d-flex align-center mb-4 mt-4">
            <v-col cols="2">
                <v-btn color="primary" @click="showAddPagoDialog">Agregar Pago</v-btn>
            </v-col>
            <v-col cols="2">
                <v-select v-model="filterMonto" :items="montoOptions" label="Filtro por monto"></v-select>
            </v-col>
            <v-col cols="2">
                <v-text-field v-model="fechaDesde" label="Fecha desde" type="date"></v-text-field>
            </v-col>
            <v-col cols="2">
                <v-text-field v-model="fechaHasta" label="Fecha hasta" type="date"></v-text-field>
            </v-col>
            <v-col cols="2">
                <v-btn color="grey" @click="limpiarFiltros">Limpiar Filtros</v-btn>
            </v-col>
        </v-row>

        <!-- Tabla de pagos -->
        <v-data-table :headers="tableHeaders" :items="filteredPagos" :items-per-page="10"
            class="elevation-1 large-table" :footer-props="{ 'items-per-page-options': [10, 20, 30] }">
            <template #item="{ item }">
                <tr>
                    <td>{{ 'Cita N° ' + item.cita_id }}</td>
                    <td>{{ formatCurrency(item.monto) }}</td>
                    <td>{{ item.fecha_pago }}</td>
                    <td class="action-buttons">
                        <div class="d-flex justify-center">
                            <v-btn size="small" color="error" class="mx-1"
                                @click="confirmDeletePago(item)" :disabled="userRole === 'Doctor'">Eliminar</v-btn>
                        </div>
                    </td>
                </tr>
            </template>
        </v-data-table>

        <!-- Diálogo para agregar/editar pago -->
        <v-dialog v-model="dialog" max-width="600px">
            <v-card>
                <v-card-title>
                    <span class="headline">{{ editMode ? 'Editar Pago' : 'Agregar Pago' }}</span>
                </v-card-title>
                <v-card-text>
                    <v-form ref="form" v-model="valid">
                        <v-select :items="citas" v-model="editedPago.cita_id" label="Cita" required item-title="id_cita"
                            item-value="id_cita">
                            <template #item="{ item }">
                                <div class="d-flex justify-space-between align-center">
                                    <span style="margin-left: 15px;">{{ 'Cita N° ' + item.value }}</span>
                                    <v-icon @click.stop="viewCitaDetails(item.value)"
                                        style="margin-right: 15px;">mdi-eye</v-icon>
                                </div>
                            </template>
                        </v-select>

                        <v-text-field v-model="editedPago.monto" label="Monto del Pago" type="number" min="0" disabled
                            required></v-text-field>

                        <v-text-field v-model="editedPago.fecha_pago" label="Fecha del Pago" type="date" disabled
                            required></v-text-field>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="closeDialog">Cancelar</v-btn>
                    <v-btn :disabled="!canSave" color="blue darken-1" text @click="savePago">Pagar</v-btn>
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
                    <p><strong>Monto: </strong>{{ '$' + citaDetails?.monto_consulta || "0" }}</p>
                    <p><strong>Estado:</strong> {{ citaDetails?.estado }}</p>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="viewCitaDialog = false">Cerrar</v-btn>
                    <v-btn color="green darken-1" text @click="selectCita">Seleccionar</v-btn>
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
import pagosService from "@/services/pagosService";
import citasService from "@/services/citasService";
import { useRouter } from 'vue-router';

const router = useRouter();
const isMini = ref(true);
const dialog = ref(false);
const editMode = ref(false);
const editedPago = ref({ cita_id: null, monto: "", fecha_pago: "" });
const pagoToDelete = ref(null);
const valid = ref(false);
const pagos = ref([]);
const citas = ref([]);
const viewCitaDialog = ref(false);
const citaDetails = ref(null);

// Filtros
const fechaDesde = ref("");
const fechaHasta = ref("");
const filterMonto = ref("Todos");
const montoOptions = ref([
    'Todos', 
    '$1 a $10', 
    '$11 a $20', 
    '$21 a $30', 
    '$31 a $40', 
    '$41 a $50', 
    '$51 a $60', 
    '$61 a $70', 
    '$71 a $80', 
    '$81 a $90', 
    '$91 a $100', 
    'Más de $100'
]);

const dynamicDialog = ref(false);
const dialogMessage = ref("");
const dialogType = ref("alert");
const dialogAction = ref(null);

// Obtener rol del usuario y paciente actual de localStorage
const userRole = localStorage.getItem('user_role');

const tableHeaders = [
    { title: 'ID de Cita', align: 'start', key: 'cita_id', width: '150px' },
    { title: 'Monto', align: 'start', key: 'monto', width: '150px' },
    { title: 'Fecha del Pago', align: 'start', key: 'fecha_pago', width: '150px' },
    { title: 'Acciones', align: 'center', key: 'actions', sortable: false, width: '150px' }
];

const canSave = computed(() => {
    return editedPago.value.cita_id && editedPago.value.monto && editedPago.value.fecha_pago;
});

function limpiarFiltros() {
    filterMonto.value = "Todos";
    fechaDesde.value = "";
    fechaHasta.value = "";
}

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

function getCurrentDate() {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

async function getPagos() {
    const response = await pagosService.obtenerPagos();
    pagos.value = response;
}

async function getCitas() {
    const response = await citasService.obtenerCitas();
    //mapear citas e incluir el id_cita
    citas.value = response
        .filter(cita => cita.pagada === 0)
        .map(cita => {
            return {
                ...cita,
                id_cita: cita.id_cita
            };
        });
    console.log("Citas no pagadas:", citas.value);
}

function selectCita() {
    if (citaDetails.value) {
        editedPago.value.cita_id = citaDetails.value.id_cita;
        editedPago.value.monto = citaDetails.value.monto_consulta;
    }
    viewCitaDialog.value = false;
}


function viewCitaDetails(id) {
    citaDetails.value = citas.value.find(cita => cita.id_cita === id);
    viewCitaDialog.value = true;
}

// Función para filtrar los pagos con los nuevos criterios
const filteredPagos = computed(() => {
    return pagos.value.filter(pago => {
        const fechaPago = new Date(pago.fecha_pago);
        const desde = fechaDesde.value ? new Date(fechaDesde.value) : null;
        const hasta = fechaHasta.value ? new Date(fechaHasta.value) : null;

        const fechaMatch = (!desde || fechaPago >= desde) && (!hasta || fechaPago <= hasta);

        const montoMatch = (() => {
            switch (filterMonto.value) {
                case "$1 a $10": return pago.monto >= 1 && pago.monto <= 10;
                case "$11 a $20": return pago.monto >= 11 && pago.monto <= 20;
                case "$21 a $30": return pago.monto >= 21 && pago.monto <= 30;
                case "$31 a $40": return pago.monto >= 31 && pago.monto <= 40;
                case "$41 a $50": return pago.monto >= 41 && pago.monto <= 50;
                case "$51 a $60": return pago.monto >= 51 && pago.monto <= 60;
                case "$61 a $70": return pago.monto >= 61 && pago.monto <= 70;
                case "$71 a $80": return pago.monto >= 71 && pago.monto <= 80;
                case "$81 a $90": return pago.monto >= 81 && pago.monto <= 90;
                case "$91 a $100": return pago.monto >= 91 && pago.monto <= 100;
                case "Más de $100": return pago.monto > 100;
                default: return true;
            }
        })();

        return fechaMatch && montoMatch;
    });
});

function formatCurrency(value) {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD"
    }).format(value);
}

onMounted(() => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        getPagos();
        getCitas();
    } else {
        router.push({ name: 'Login' });
    }
});

function toggleDrawer() {
    isMini.value = !isMini.value;
}

function showAddPagoDialog() {
    editMode.value = false;
    editedPago.value = {
        cita_id: "",
        monto: "",
        fecha_pago: getCurrentDate() // Asignar la fecha actual
    };
    dialog.value = true;
}

function confirmDeletePago(pago) {
    pagoToDelete.value = pago;
    showDialog(
        `¿Estás seguro de que deseas eliminar el pago de la cita N° ${pago.cita_id}?`,
        "confirm",
        async () => await deletePago()
    );
}

async function deletePago() {
    try {
        await pagosService.eliminarPago(pagoToDelete.value.id_pago);
        await getPagos();
        pagoToDelete.value = null;
    } catch (error) {
        showDialog(error.message || "Error al eliminar el pago", "alert");
    }
}

async function savePago() {
    try {
        await pagosService.crearPago(editedPago.value);
        closeDialog();
        await getPagos();
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
