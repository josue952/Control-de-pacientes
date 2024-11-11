import axios from "axios";

const API_URL = "http://127.0.0.1:8000/api/pagos";

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

const pagosService = {
    // Obtener todos los pagos
    async obtenerPagos() {
        try {
            const response = await axios.get(API_URL);
            return response.data;
        } catch (error) {
            this.handleError(error, "Error al obtener pagos");
        }
    },

    // Obtener un pago específico por ID
    async obtenerPagoPorId(id) {
        try {
            const response = await axios.get(`${API_URL}/${id}`);
            return response.data;
        } catch (error) {
            this.handleError(error, `Error al obtener el pago con ID ${id}`);
        }
    },

    // Crear un nuevo pago
    async crearPago(pagoData) {
        try {
            const response = await axios.post(`${API_URL}`, pagoData);
            return response.data;
        } catch (error) {
            console.error("Error al crear el pago", error.response?.data || error.message);
            this.handleError(error, "Error al crear el pago");
        }
    },

    // Eliminar un pago por ID
    async eliminarPago(id) {
        try {
            const response = await axios.delete(`${API_URL}/${id}`);
            return response.data;
        } catch (error) {
            this.handleError(error, `Error al eliminar el pago con ID ${id}`);
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

export default pagosService;
