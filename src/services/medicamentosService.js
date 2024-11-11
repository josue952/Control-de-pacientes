import axios from "axios";

const API_URL = "http://127.0.0.1:8000/api/medicamentos";

// Configuración de Axios para incluir el token en cada solicitud
axios.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem("auth_token");
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

const medicamentosService = {
    // Obtener todos los medicamentos
    async obtenerMedicamentos() {
        try {
            const response = await axios.get(API_URL);
            return response.data;
        } catch (error) {
            this.handleError(error, "Error al obtener medicamentos");
        }
    },

    // Obtener un medicamento específico por ID
    async obtenerMedicamentoPorId(id) {
        try {
            const response = await axios.get(`${API_URL}/${id}`);
            return response.data;
        } catch (error) {
            this.handleError(error, `Error al obtener el medicamento con ID ${id}`);
        }
    },

    // Crear un nuevo medicamento
    async crearMedicamento(medicamentoData) {
        try {
            const response = await axios.post(`${API_URL}`, medicamentoData);
            return response.data;
        } catch (error) {
            console.error("Error al crear el medicamento", error.response?.data || error.message);
            this.handleError(error, "Error al crear el medicamento");
        }
    },

    // Actualizar un medicamento existente por ID
    async actualizarMedicamento(id, medicamentoData) {
        try {
            const response = await axios.put(`${API_URL}/${id}`, medicamentoData);
            return response.data;
        } catch (error) {
            console.error("Error al actualizar el medicamento", error.response?.data || error.message);
            this.handleError(error, `Error al actualizar el medicamento con ID ${id}`);
        }
    },

    // Eliminar un medicamento por ID
    async eliminarMedicamento(id) {
        try {
            const response = await axios.delete(`${API_URL}/${id}`);
            return response.data;
        } catch (error) {
            this.handleError(error, `Error al eliminar el medicamento con ID ${id}`);
        }
    },

    // Manejo de errores
    handleError(error, customMessage) {
        if (error.response) {
            console.error(customMessage, error.response.data);
            throw error.response.data.message || error.response.data.errors || "Ocurrió un error inesperado";
        } else {
            console.error(customMessage, error.message);
            throw error.message || "Ocurrió un error inesperado";
        }
    }
};

export default medicamentosService;
