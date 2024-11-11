import axios from "axios";

const API_URL = "http://127.0.0.1:8000/api/recetas";

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

const recetasService = {
    // Obtener todas las recetas
    async obtenerRecetas() {
        try {
            const response = await axios.get(API_URL);
            return response.data;
        } catch (error) {
            this.handleError(error, "Error al obtener recetas");
        }
    },

    // Obtener una receta específica por ID
    async obtenerRecetaPorId(id) {
        try {
            const response = await axios.get(`${API_URL}/${id}`);
            return response.data;
        } catch (error) {
            this.handleError(error, `Error al obtener la receta con ID ${id}`);
        }
    },

    // Crear una nueva receta
    async crearReceta(recetaData) {
        try {
            const response = await axios.post(`${API_URL}`, recetaData);
            return response.data;
        } catch (error) {
            console.error("Error al crear la receta", error.response?.data || error.message);
            this.handleError(error, "Error al crear la receta");
        }
    },

    // Actualizar una receta existente por ID
    async actualizarReceta(id, recetaData) {
        try {
            const response = await axios.put(`${API_URL}/${id}`, recetaData);
            return response.data;
        } catch (error) {
            console.error("Error al actualizar la receta", error.response?.data || error.message);
            this.handleError(error, `Error al actualizar la receta con ID ${id}`);
        }
    },

    // Eliminar una receta por ID
    async eliminarReceta(id) {
        try {
            const response = await axios.delete(`${API_URL}/${id}`);
            return response.data;
        } catch (error) {
            this.handleError(error, `Error al eliminar la receta con ID ${id}`);
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

export default recetasService;
