import axios from "axios";

const API_URL = "http://127.0.0.1:8000/api/usuarios";

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

const usuariosService = {
    // Obtener todos los usuarios
    async obtenerUsuarios() {
        try {
            const response = await axios.get(API_URL);
            return response.data;
        } catch (error) {
            this.handleError(error, "Error al obtener usuarios");
        }
    },

    // Obtener un usuario específico por ID
    async obtenerUsuarioPorId(id) {
        try {
            const response = await axios.get(`${API_URL}/${id}`);
            return response.data;
        } catch (error) {
            this.handleError(error, `Error al obtener el usuario con ID ${id}`);
        }
    },

    // Crear un nuevo usuario
    async crearUsuario(usuarioData) {
        try {
            const response = await axios.post(API_URL, usuarioData);
            return response.data;
        } catch (error) {
            this.handleError(error, "Error al crear el usuario");
        }
    },

    // Actualizar un usuario existente por ID
    async actualizarUsuario(id, usuarioData) {
        try {
            const response = await axios.put(`${API_URL}/${id}`, usuarioData);
            return response.data;
        } catch (error) {
            this.handleError(error, `Error al actualizar el usuario con ID ${id}`);
        }
    },

    // Eliminar un usuario por ID
    async eliminarUsuario(id) {
        try {
            const response = await axios.delete(`${API_URL}/${id}`);
            return response.data;
        } catch (error) {
            this.handleError(error, `Error al eliminar el usuario con ID ${id}`);
        }
    },

    // Método para cerrar sesión
    async cerrarSesion() {
        try {
            await axios.post("/api/logout");
        } catch (error) {
            console.error("Error al cerrar sesión en el backend:", error);
            throw error;
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

export default usuariosService;
