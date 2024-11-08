<template>
    <v-app>
        <header-component @toggle-mini-variant="toggleDrawer" />
        <side-bar :is-mini="isMini" @update:isMini="isMini = $event" />
    </v-app>
</template>
<script>
import SideBar from '../components/SideBar.vue'
import HeaderComponent from '../components/HeaderComponent.vue'

export default {
    components: {
        SideBar,
        HeaderComponent
    },
    data() {
        return {
            isMini: true,
        };
    },
    methods: {
        toggleDrawer() {
            this.isMini = !this.isMini;
        },
    },
}
</script>