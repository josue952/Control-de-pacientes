import axios from "axios";

const API_URL = "http://127.0.0.1:8000/api/doctores";

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

const doctoresService = {
    // Obtener todos los doctores
    async obtenerDoctores() {
        try {
            const response = await axios.get(API_URL);
            return response.data;
        } catch (error) {
            this.handleError(error, "Error al obtener doctores");
        }
    },

    // Obtener un doctor específico por ID
    async obtenerDoctorPorId(id) {
        try {
            const response = await axios.get(`${API_URL}/${id}`);
            return response.data;
        } catch (error) {
            this.handleError(error, `Error al obtener el doctor con ID ${id}`);
        }
    },

    // Crear un nuevo doctor
    async crearDoctor(doctorData) {
        try {
            const response = await axios.post(API_URL, doctorData);
            return response.data;
        } catch (error) {
            this.handleError(error, "Error al crear el doctor");
        }
    },

    // Actualizar un doctor existente por ID
    async actualizarDoctor(id, doctorData) {
        try {
            const response = await axios.put(`${API_URL}/${id}`, doctorData);
            return response.data;
        } catch (error) {
            this.handleError(error, `Error al actualizar el doctor con ID ${id}`);
        }
    },

    // Eliminar un doctor por ID
    async eliminarDoctor(id) {
        try {
            const response = await axios.delete(`${API_URL}/${id}`);
            return response.data;
        } catch (error) {
            this.handleError(error, `Error al eliminar el doctor con ID ${id}`);
        }
    },

    // Manejo de errores
    handleError(error, customMessage) {
        if (error.response) {
            console.error(customMessage, error.response.data);
            throw error.response.data.errors || error.response.data.message || "Ocurrió un error";
        } else {
            console.error(customMessage, error.message);
            throw error.message || "Ocurrió un error inesperado";
        }
    }
};

export default doctoresService;
