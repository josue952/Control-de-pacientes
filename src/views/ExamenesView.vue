<template>
    <div class="nav">
        <header-component @toggle-mini-variant="toggleDrawer" />
        <side-bar :is-mini="isMini" @update:isMini="isMini = $event" />
    </div>
    <v-container class="centered-container content-padding">
        <!-- Barra superior con botón Agregar y filtros -->
        <v-row class="d-flex align-center mb-4 mt-4">
            <v-col cols="4">
                <v-btn color="primary" @click="showAddExamenDialog">Agregar Examen</v-btn>
            </v-col>
            <v-col cols="4">
                <v-select
                    v-model="filterTipoExamen"
                    :items="['Todos', ...examenTypes]"
                    label="Filtro por tipo de examen"
                ></v-select>
            </v-col>
            <v-col cols="4">
                <v-select
                    v-model="filterResultados"
                    :items="['Todos', ...resultOptions]"
                    label="Filtro por resultados"
                ></v-select>
            </v-col>
        </v-row>

        <!-- Tabla de exámenes -->
        <v-data-table :headers="tableHeaders" :items="filteredExamenes" :items-per-page="10" class="elevation-1 large-table"
            :footer-props="{ 'items-per-page-options': [10, 20, 30] }">
            <template #item="{ item }">
                <tr>
                    <td>{{ item.tipo_examen }}</td>
                    <td>{{ item.descripcion }}</td>
                    <td>{{ item.fecha_examen }}</td>
                    <td>{{ item.resultados }}</td>
                    <td>{{ item.observaciones }}</td>
                    <td class="action-buttons">
                        <div class="d-flex justify-center">
                            <v-btn size="small" color="primary" class="mx-1" @click="editExamen(item)">Editar</v-btn>
                            <v-btn size="small" color="error" class="mx-1" @click="confirmDeleteExamen(item)" :disabled="userRole === 'Paciente'">Eliminar</v-btn>
                        </div>
                    </td>
                </tr>
            </template>
        </v-data-table>

        <!-- Diálogo para agregar/editar examen -->
        <v-dialog v-model="dialog" max-width="600px">
            <v-card>
                <v-card-title>
                    <span class="headline">{{ editMode ? 'Editar Examen' : 'Agregar Examen' }}</span>
                </v-card-title>
                <v-card-text>
                    <v-form ref="form" v-model="valid">
                        <v-select
                            :items="examenTypes"
                            v-model="editedExamen.tipo_examen"
                            label="Tipo de Examen"
                            required
                        ></v-select>

                        <v-text-field v-model="editedExamen.descripcion" label="Descripción" type="text"></v-text-field>

                        <v-text-field v-model="editedExamen.fecha_examen" label="Fecha del Examen" type="date" required></v-text-field>

                        <v-select
                            :items="resultOptions"
                            v-model="editedExamen.resultados"
                            label="Resultados"
                            required
                        ></v-select>

                        <v-text-field v-model="editedExamen.observaciones" label="Observaciones" type="text"></v-text-field>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="closeDialog">Cancelar</v-btn>
                    <v-btn :disabled="!canSave" color="blue darken-1" text @click="saveExamen">Guardar</v-btn>
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
import examenesService from "@/services/examenesService";
import { useRouter } from 'vue-router';

const router = useRouter();
const isMini = ref(true);
const dialog = ref(false);
const editMode = ref(false);
const editedExamen = ref({ tipo_examen: "", descripcion: "", fecha_examen: "", resultados: "", observaciones: "" });
const examenToDelete = ref(null);
const valid = ref(false);
const examenes = ref([]);

// Opciones para el campo "Tipo de Examen"
const examenTypes = [
    "Hemograma Completo",
    "Perfil Lipídico",
    "Glucosa en Sangre",
    "Función Hepática",
    "Función Renal",
    "Análisis de Orina",
    "Examen de Tiroides",
    "Perfil Electrocardiográfico",
    "Prueba de VIH",
    "Prueba de COVID-19"
];

// Opciones para el campo "Resultados"
const resultOptions = ["Positivo", "Negativo", "En Proceso", "Cancelado"];

// Filtros para tipo de examen y resultados
const filterTipoExamen = ref("Todos");
const filterResultados = ref("Todos");

const dynamicDialog = ref(false);
const dialogMessage = ref("");
const dialogType = ref("alert");
const dialogAction = ref(null);

// Obtener rol del usuario y paciente actual de localStorage
const userRole = localStorage.getItem('user_role');

const tableHeaders = [
    { title: 'Tipo de Examen', align: 'center', key: 'tipo_examen', width: '150px' },
    { title: 'Descripción', align: 'center', key: 'descripcion', width: '250px' },
    { title: 'Fecha del Examen', align: 'center', key: 'fecha_examen', width: '150px' },
    { title: 'Resultados', align: 'center', key: 'resultados', width: '250px' },
    { title: 'Observaciones', align: 'center', key: 'observaciones', width: '250px' },
    { title: 'Acciones', align: 'center', key: 'actions', sortable: false, width: '150px' }
];

const canSave = computed(() => {
    return editedExamen.value.tipo_examen && editedExamen.value.fecha_examen && editedExamen.value.resultados;
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

async function getExamenes() {
    const response = await examenesService.obtenerExamenes();
    examenes.value = response;
    console.log('Exámenes cargados:', examenes.value);
}

const filteredExamenes = computed(() => {
    return examenes.value.filter(examen => {
        const tipoExamenMatch = filterTipoExamen.value === "Todos" || examen.tipo_examen === filterTipoExamen.value;
        const resultadosMatch = filterResultados.value === "Todos" || examen.resultados === filterResultados.value;
        return tipoExamenMatch && resultadosMatch;
    });
})

onMounted(() => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        getExamenes();
    } else {
        router.push({ name: 'Login' });
    }
});

function toggleDrawer() {
    isMini.value = !isMini.value;
}

function showAddExamenDialog() {
    editMode.value = false;
    editedExamen.value = {
        tipo_examen: "",
        descripcion: "",
        fecha_examen: "",
        resultados: "",
        observaciones: ""
    };
    dialog.value = true;
}

function editExamen(examen) {
    editMode.value = true;
    editedExamen.value = {
        ...examen, // Copia todos los campos de examen, incluyendo el ID
        id_examen: examen.id_examen // Asegúrate de que el ID esté incluido explícitamente
    };
    dialog.value = true;
}

function confirmDeleteExamen(examen) {
    examenToDelete.value = examen;
    showDialog(
        `¿Estás seguro de que deseas eliminar el examen de tipo "${examen.tipo_examen}"?`,
        "confirm",
        async () => await deleteExamen()
    );
}

async function deleteExamen() {
    try {
        await examenesService.eliminarExamen(examenToDelete.value.id_examen);
        await getExamenes();
        examenToDelete.value = null;
    } catch (error) {
        showDialog(error.message || "Error al eliminar el examen", "alert");
    }
}

async function saveExamen() {
    try {
        if (editMode.value) {
            // Revisa que el ID esté presente al actualizar
            console.log('Examen a actualizar: ', editedExamen.value);
            await examenesService.actualizarExamen(editedExamen.value.id_examen, editedExamen.value);
        } else {
            console.log('Examen a guardar:', editedExamen.value);
            await examenesService.crearExamen(editedExamen.value);
        }
        closeDialog();
        await getExamenes();
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
