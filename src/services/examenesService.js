import axios from "axios";

const API_URL = "http://127.0.0.1:8000/api/examenes";

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

const examenesService = {
    // Obtener todos los exámenes
    async obtenerExamenes() {
        try {
            const response = await axios.get(API_URL);
            return response.data;
        } catch (error) {
            this.handleError(error, "Error al obtener exámenes");
        }
    },

    // Obtener un examen específico por ID
    async obtenerExamenPorId(id) {
        try {
            const response = await axios.get(`${API_URL}/${id}`);
            return response.data;
        } catch (error) {
            this.handleError(error, `Error al obtener el examen con ID ${id}`);
        }
    },

    // Crear un nuevo examen
    async crearExamen(examenData) {
        try {
            const response = await axios.post(`${API_URL}`, examenData);
            return response.data;
        } catch (error) {
            console.error("Error al crear el examen", error.response?.data || error.message);
            this.handleError(error, "Error al crear el examen");
        }
    },

    // Actualizar un examen existente por ID
    async actualizarExamen(id, examenData) {
        try {
            const response = await axios.put(`${API_URL}/${id}`, examenData);
            return response.data;
        } catch (error) {
            console.error("Error al actualizar el examen", error.response?.data || error.message);
            this.handleError(error, `Error al actualizar el examen con ID ${id}`);
        }
    },

    // Eliminar un examen por ID
    async eliminarExamen(id) {
        try {
            const response = await axios.delete(`${API_URL}/${id}`);
            return response.data;
        } catch (error) {
            this.handleError(error, `Error al eliminar el examen con ID ${id}`);
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

export default examenesService;
