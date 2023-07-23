<template>
    <div class="flex w-3/4 mx-auto">
    <div  class="w-1/2">
        <h3>Пользователи</h3>
        <div v-if="users">
            <div v-for="user in users" class=" flex border-b-2 p-3 items-center">
                <p class="mx-2">{{user.id + ". " + user.name }}</p>
                <a @click.prevent="store(user.id)" class="mx-2 p-2 inline-block bg-slate-500 text-white rounded" href="#">Написать...</a>
            </div>
        </div>  
    </div> 
    </div>
    
</template>
<script>
import {Link} from "@inertiajs/vue3"
import Main from "@/Layouts/Main.vue"
    export default{
        name: "Index",
        props: [
            'users',
            'chats'
        ],
        components: {
            Link
        },
        layout: Main,
        methods:{
            store(id) {
                this.$inertia.post('/chats', {title: null, users: [id]})
            }
        }
    }
</script>
<style scoped>

</style>