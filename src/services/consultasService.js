import axios from "axios";

const API_URL = "http://127.0.0.1:8000/api/consultas";

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

const consultasService = {
    // Obtener todas las consultas
    async obtenerConsultas() {
        try {
            const response = await axios.get(API_URL);
            return response.data;
        } catch (error) {
            this.handleError(error, "Error al obtener consultas");
        }
    },

    // Obtener una consulta específica por ID
    async obtenerConsultaPorId(id) {
        try {
            const response = await axios.get(`${API_URL}/${id}`);
            return response.data;
        } catch (error) {
            this.handleError(error, `Error al obtener la consulta con ID ${id}`);
        }
    },

    // Crear una nueva consulta
    async crearConsulta(consultaData) {
        try {
            const response = await axios.post(`${API_URL}`, consultaData);
            return response.data;
        } catch (error) {
            console.error("Error al crear la consulta", error.response?.data || error.message);
            this.handleError(error, "Error al crear la consulta");
        }
    },

    // Actualizar una consulta existente por ID
    async actualizarConsulta(id, consultaData) {
        try {
            const response = await axios.put(`${API_URL}/${id}`, consultaData);
            return response.data;
        } catch (error) {
            console.error("Error al actualizar la consulta", error.response?.data || error.message);
            this.handleError(error, `Error al actualizar la consulta con ID ${id}`);
        }
    },

    // Eliminar una consulta por ID
    async eliminarConsulta(id) {
        try {
            const response = await axios.delete(`${API_URL}/${id}`);
            return response.data;
        } catch (error) {
            this.handleError(error, `Error al eliminar la consulta con ID ${id}`);
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

export default consultasService;
