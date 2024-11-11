import axios from "axios";

const API_URL = "http://127.0.0.1:8000/api/citas";

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

const citasService = {
    // Obtener todas las citas
    async obtenerCitas() {
        try {
            const response = await axios.get(API_URL);
            return response.data;
        } catch (error) {
            this.handleError(error, "Error al obtener citas");
        }
    },

    // Obtener una cita específica por ID
    async obtenerCitaPorId(id) {
        try {
            const response = await axios.get(`${API_URL}/${id}`);
            return response.data;
        } catch (error) {
            this.handleError(error, `Error al obtener la cita con ID ${id}`);
        }
    },

    // Crear una nueva cita
    async crearCita(citaData) {
        try {
            const response = await axios.post(`${API_URL}`, citaData);
            return response.data;
        } catch (error) {
            console.error("Error al crear la cita", error.response?.data || error.message);
            this.handleError(error, "Error al crear la cita");
        }
    },

    // Actualizar una cita existente por ID
    async actualizarCita(id, citaData) {
        try {
            const response = await axios.put(`${API_URL}/${id}`, citaData);
            return response.data;
        } catch (error) {
            console.error("Error al actualizar la cita", error.response?.data || error.message);
            this.handleError(error, `Error al actualizar la cita con ID ${id}`);
        }
    },

    // Eliminar una cita por ID
    async eliminarCita(id) {
        try {
            const response = await axios.delete(`${API_URL}/${id}`);
            return response.data;
        } catch (error) {
            this.handleError(error, `Error al eliminar la cita con ID ${id}`);
        }
    },

    // Manejo de errores
    handleError(error, customMessage) {
        if (error.response) {
            console.error(customMessage, error.response.data);
            // Si el backend envía un mensaje o un array de errores, lanza esa información específica
            throw error.response.data.message || error.response.data.errors || "Ocurrió un error inesperado";
        } else {
            console.error(customMessage, error.message);
            throw error.message || "Ocurrió un error inesperado";
        }
    }
};

export default citasService;
