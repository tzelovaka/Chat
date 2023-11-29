    <template>
        <div>
            <div>
               <div class="flex p-4 bg-gray-600 text-white mb-8">
                <Link class="mx-4" :href="route('index')">Главная</Link>
                <Link class="mx-4" :href="route('chats.index')">Чаты</Link>
                <Link class="mx-4" :href="route('dashboard')">Профиль</Link>
               </div>
            </div>
            <slot />
        </div>
    </template>
    <script>
       import {Link} from "@inertiajs/vue3"
        export default{
        name: "Main",
        components: {
            Link,
        }
    }
    </script>
    <style scoped>
    
    </style>