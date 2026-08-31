<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BadgePercent,
    Boxes,
    CreditCard,
    FileClock,
    Gem,
    Images,
    LayoutGrid,
    Package,
    ScrollText,
    Settings,
    ShieldCheck,
    ShoppingCart,
    Sparkles,
    Tags,
    Ticket,
    Truck,
    Users,
} from '@lucide/vue';
import { computed } from 'vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';

const page = usePage<{ auth: { permissions: string[] } }>();
const { isCurrentOrParentUrl } = useCurrentUrl();

function can(permission: string): boolean {
    return page.props.auth.permissions.includes(permission);
}

const groups = computed(() =>
    [
        {
            label: 'Catálogo',
            items: [
                {
                    title: 'Productos',
                    href: '/admin/productos',
                    icon: Gem,
                    show: can('products.view'),
                },
                {
                    title: 'Categorías',
                    href: '/admin/categorias',
                    icon: Tags,
                    show: can('categories.manage'),
                },
                {
                    title: 'Colecciones',
                    href: '/admin/colecciones',
                    icon: Sparkles,
                    show: can('collections.manage'),
                },
                {
                    title: 'Materiales y piedras',
                    href: '/admin/materiales',
                    icon: Package,
                    show: can('materials.manage'),
                },
            ],
        },
        {
            label: 'Inventario',
            items: [
                {
                    title: 'Existencias y movimientos',
                    href: '/admin/inventario',
                    icon: Boxes,
                    show: can('inventory.view'),
                },
            ],
        },
        {
            label: 'Ventas',
            items: [
                {
                    title: 'Pedidos',
                    href: '/admin/pedidos',
                    icon: ShoppingCart,
                    show: can('orders.view'),
                },
                {
                    title: 'Clientes',
                    href: '/admin/clientes',
                    icon: Users,
                    show: can('customers.view'),
                },
                {
                    title: 'Métodos de envío',
                    href: '/admin/envios',
                    icon: Truck,
                    show: can('orders.view'),
                },
                {
                    title: 'Meses sin intereses',
                    href: '/admin/msi',
                    icon: CreditCard,
                    show: can('settings.manage'),
                },
            ],
        },
        {
            label: 'Marketing',
            items: [
                {
                    title: 'Promociones',
                    href: '/admin/promociones',
                    icon: BadgePercent,
                    show: can('promotions.view'),
                },
                {
                    title: 'Cupones',
                    href: '/admin/cupones',
                    icon: Ticket,
                    show: can('coupons.view'),
                },
                {
                    title: 'Banners',
                    href: '/admin/banners',
                    icon: Images,
                    show: can('banners.manage'),
                },
            ],
        },
        {
            label: 'Reportes',
            items: [
                {
                    title: 'Reportes',
                    href: '/admin/reportes',
                    icon: LayoutGrid,
                    show: can('reports.view'),
                },
            ],
        },
        {
            label: 'Usuarios',
            items: [
                {
                    title: 'Usuarios',
                    href: '/admin/usuarios',
                    icon: Users,
                    show: can('users.manage'),
                },
                {
                    title: 'Roles y permisos',
                    href: '/admin/roles',
                    icon: ShieldCheck,
                    show: can('roles.manage'),
                },
            ],
        },
        {
            label: 'Sistema',
            items: [
                {
                    title: 'Configuración',
                    href: '/admin/configuracion',
                    icon: Settings,
                    show: can('settings.manage'),
                },
                {
                    title: 'Páginas legales',
                    href: '/admin/paginas-legales',
                    icon: ScrollText,
                    show: can('settings.manage'),
                },
                {
                    title: 'Auditoría',
                    href: '/admin/auditoria',
                    icon: FileClock,
                    show: can('audit.view'),
                },
            ],
        },
    ]
        .map((group) => ({
            ...group,
            items: group.items.filter((item) => item.show),
        }))
        .filter((group) => group.items.length > 0),
);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/admin">
                            <span class="font-display text-lg">Vrenelle</span>
                            <span class="ml-1 text-xs text-muted-foreground"
                                >Admin</span
                            >
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <SidebarGroup
                v-for="group in groups"
                :key="group.label"
                class="px-2 py-0"
            >
                <SidebarGroupLabel>{{ group.label }}</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem
                        v-for="item in group.items"
                        :key="item.title"
                    >
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentOrParentUrl(item.href)"
                            :tooltip="item.title"
                        >
                            <Link :href="item.href">
                                <component :is="item.icon" />
                                <span>{{ item.title }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
