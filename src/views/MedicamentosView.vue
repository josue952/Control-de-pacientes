<template>
    <div class="nav">
        <header-component @toggle-mini-variant="toggleDrawer" />
        <side-bar :is-mini="isMini" @update:isMini="isMini = $event" />
    </div>
    <v-container class="centered-container content-padding">
        <!-- Barra superior con botón Agregar y filtros -->
        <v-row class="d-flex align-center mb-4 mt-4">
            <v-col cols="4">
                <v-btn color="primary" @click="showAddMedicamentoDialog">Agregar Medicamento</v-btn>
            </v-col>
            <v-col cols="4">
                <v-text-field v-model="filterNombre" label="Filtro por nombre"></v-text-field>
            </v-col>
            <v-col cols="4">
                <v-select v-model="selectedCantidadRange" :items="cantidadOptions" label="Filtro por Cantidad"></v-select>
            </v-col>
        </v-row>

        <!-- Tabla de medicamentos -->
        <v-data-table :headers="tableHeaders" :items="filteredMedicamentos" :items-per-page="10"
            class="elevation-1 large-table" :footer-props="{ 'items-per-page-options': [10, 20, 30] }">
            <template #item="{ item }">
                <tr>
                    <td>{{ item.nombre }}</td>
                    <td>{{ item.descripcion || "No especificado" }}</td>
                    <td>{{ item.cantidad }}</td>
                    <td>{{ item.dosis || "No especificado" }}</td>
                    <td>{{ item.fecha_registro }}</td>
                    <td class="action-buttons">
                        <div class="d-flex justify-center">
                            <v-btn size="small" color="primary" class="mx-1" @click="editMedicamento(item)">Editar</v-btn>
                            <v-btn size="small" color="error" class="mx-1" @click="confirmDeleteMedicamento(item)" :disabled="userRole === 'Paciente'">Eliminar</v-btn>
                        </div>
                    </td>
                </tr>
            </template>
        </v-data-table>

        <!-- Diálogo para agregar/editar medicamento -->
        <v-dialog v-model="dialog" max-width="600px">
            <v-card>
                <v-card-title>
                    <span class="headline">{{ editMode ? 'Editar Medicamento' : 'Agregar Medicamento' }}</span>
                </v-card-title>
                <v-card-text>
                    <v-form ref="form" v-model="valid">
                        <v-text-field v-model="editedMedicamento.nombre" label="Nombre" required></v-text-field>
                        <v-textarea v-model="editedMedicamento.descripcion" label="Descripción"></v-textarea>
                        <v-text-field
                            v-model="editedMedicamento.cantidad"
                            label="Cantidad"
                            type="number"
                            :rules="[cantidadMinimaRule]"
                            min="1"
                        ></v-text-field>
                        <v-text-field v-model="editedMedicamento.dosis" label="Dosis"></v-text-field>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="closeDialog">Cancelar</v-btn>
                    <v-btn :disabled="!canSave" color="blue darken-1" text @click="saveMedicamento">Guardar</v-btn>
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
import medicamentosService from "@/services/medicamentosService";
import { useRouter } from 'vue-router';

const router = useRouter();
const isMini = ref(true);
const dialog = ref(false);
const editMode = ref(false);
const editedMedicamento = ref({ nombre: "", descripcion: "", cantidad: "", dosis: "" });
const medicamentoToDelete = ref(null);
const valid = ref(false);
const medicamentos = ref([]);

// Filtros
const filterNombre = ref("");
const selectedCantidadRange = ref("Todos");
const cantidadOptions = [
    "Todos",
    "1 a 10",
    "11 a 20",
    "21 a 30",
    "31 a 40",
    "41 a 50",
    "51 a 60",
    "61 a 70",
    "71 a 80",
    "81 a 90",
    "91 a 100",
    "Más de 100"
];

const dynamicDialog = ref(false);
const dialogMessage = ref("");
const dialogType = ref("alert");
const dialogAction = ref(null);

// Obtener rol del usuario y paciente actual de localStorage
const userRole = localStorage.getItem('user_role');

const tableHeaders = [
    { title: 'Nombre', align: 'start', key: 'nombre', width: '150px' },
    { title: 'Descripción', align: 'start', key: 'descripcion', width: '250px' },
    { title: 'Cantidad', align: 'start', key: 'cantidad', width: '25px' },
    { title: 'Dosis Recomendada', align: 'start', key: 'dosis', width: '150px' },
    { title: 'Fecha de Registro', align: 'start', key: 'fecha_registro', width: '150px' },
    { title: 'Acciones', align: 'center', key: 'actions', sortable: false, width: '150px' }
];

// Validación personalizada para la cantidad mínima
const cantidadMinimaRule = (value) => {
    return value >= 1 || "La cantidad mínima es 1";
};

// Validar que se puede guardar
const canSave = computed(() => editedMedicamento.value.nombre && editedMedicamento.value.cantidad >= 1);

// Mostrar el diálogo de mensajes
function showDialog(message = "Ha ocurrido un error", type = "alert", action = null) {
    dialogMessage.value = message;
    dialogType.value = type;
    dialogAction.value = action ? async () => { await action(); dynamicDialog.value = false; } : null;
    dynamicDialog.value = true;
}

// Función para obtener los medicamentos
async function getMedicamentos() {
    const response = await medicamentosService.obtenerMedicamentos();
    medicamentos.value = response;
}

// Función para manejar el diálogo de confirmación de eliminación
async function handleDialogAction() {
    if (dialogAction.value) await dialogAction.value();
}

// Función para verificar el rango de cantidad seleccionado
function cantidadEnRango(cantidad, rango) {
    switch (rango) {
        case "1 a 10": return cantidad >= 1 && cantidad <= 10;
        case "11 a 20": return cantidad >= 11 && cantidad <= 20;
        case "21 a 30": return cantidad >= 21 && cantidad <= 30;
        case "31 a 40": return cantidad >= 31 && cantidad <= 40;
        case "41 a 50": return cantidad >= 41 && cantidad <= 50;
        case "51 a 60": return cantidad >= 51 && cantidad <= 60;
        case "61 a 70": return cantidad >= 61 && cantidad <= 70;
        case "71 a 80": return cantidad >= 71 && cantidad <= 80;
        case "81 a 90": return cantidad >= 81 && cantidad <= 90;
        case "91 a 100": return cantidad >= 91 && cantidad <= 100;
        case "Más de 100": return cantidad > 100;
        default: return true;
    }
}

// Filtro de medicamentos en base a los filtros seleccionados
const filteredMedicamentos = computed(() => {
    return medicamentos.value.filter(med => {
        const nombreMatch = filterNombre.value ? med.nombre.toLowerCase().includes(filterNombre.value.toLowerCase()) : true;
        const cantidadMatch = cantidadEnRango(med.cantidad, selectedCantidadRange.value);
        return nombreMatch && cantidadMatch;
    });
});

// Montar el componente y cargar datos
onMounted(() => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        getMedicamentos();
    } else {
        router.push({ name: 'Login' });
    }
});

// Mostrar y ocultar el menú lateral
function toggleDrawer() {
    isMini.value = !isMini.value;
}

// Función para mostrar el diálogo de añadir un medicamento
function showAddMedicamentoDialog() {
    editMode.value = false;
    editedMedicamento.value = { nombre: "", descripcion: "", cantidad: "", dosis: "" };
    dialog.value = true;
}

// Confirmar eliminación de un medicamento
function confirmDeleteMedicamento(medicamento) {
    medicamentoToDelete.value = medicamento;
    showDialog(
        `¿Estás seguro de que deseas eliminar el medicamento ${medicamento.nombre}?`,
        "confirm",
        async () => await deleteMedicamento()
    );
}

// Eliminar medicamento
async function deleteMedicamento() {
    try {
        await medicamentosService.eliminarMedicamento(medicamentoToDelete.value.id_medicamento);
        await getMedicamentos();
        medicamentoToDelete.value = null;
    } catch (error) {
        showDialog(error.message || "Error al eliminar el medicamento", "alert");
    }
}

// Guardar medicamento (crear o actualizar)
async function saveMedicamento() {
    try {
        if (editMode.value) {
            await medicamentosService.actualizarMedicamento(editedMedicamento.value.id_medicamento, editedMedicamento.value);
        } else {
            await medicamentosService.crearMedicamento(editedMedicamento.value);
        }
        closeDialog();
        await getMedicamentos();
    } catch (error) {
        showDialog(error, "alert");
    }
}

// Cerrar el diálogo de creación/edición de medicamentos
function closeDialog() {
    dialog.value = false;
}

// Editar medicamento
function editMedicamento(medicamento) {
    editMode.value = true;
    editedMedicamento.value = { ...medicamento };
    dialog.value = true;
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
