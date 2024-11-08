import axios from "axios";

const API_URL = "http://127.0.0.1:8000/api/pacientes";

// Configuración de Axios para incluir el token en cada solicitud
axios.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem("auth_token"); // Obtiene el token del localStorage
        if (token) {
            config.headers.Authorization = `Bearer ${token}`; // Añade el token al encabezado
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

const pacientesService = {
    // Obtener todos los pacientes
    async obtenerPacientes() {
        try {
            const response = await axios.get(API_URL);
            return response.data;
        } catch (error) {
            this.handleError(error, "Error al obtener pacientes");
        }
    },

    // Obtener un paciente específico por ID
    async obtenerPacientePorId(id) {
        try {
            const response = await axios.get(`${API_URL}/${id}`);
            return response.data;
        } catch (error) {
            this.handleError(error, `Error al obtener el paciente con ID ${id}`);
        }
    },

    // Crear un nuevo paciente
    async crearPaciente(pacienteData) {
        try {
            const response = await axios.post(API_URL, pacienteData);
            return response.data;
        } catch (error) {
            this.handleError(error, "Error al crear el paciente");
        }
    },

    // Actualizar un paciente existente por ID
    async actualizarPaciente(id, pacienteData) {
        try {
            const response = await axios.put(`${API_URL}/${id}`, pacienteData);
            return response.data;
        } catch (error) {
            this.handleError(error, `Error al actualizar el paciente con ID ${id}`);
        }
    },

    // Eliminar un paciente por ID
    async eliminarPaciente(id) {
        try {
            const response = await axios.delete(`${API_URL}/${id}`);
            return response.data;
        } catch (error) {
            this.handleError(error, `Error al eliminar el paciente con ID ${id}`);
        }
    },

    // Manejo de errores
    handleError(error, customMessage) {
        // Verifica si el error tiene una respuesta del servidor
        if (error.response) {
            console.error(customMessage, error.response.data);
            throw error.response.data.errors || error.response.data.message || "Ocurrió un error";
        } else {
            console.error(customMessage, error.message);
            throw error.message || "Ocurrió un error inesperado";
        }
    }
};

export default pacientesService;
