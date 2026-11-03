<x-admin.stat-card
    data-stat="usuarios"
    title="Usuários"
    description="Contas cadastradas"
    :value="number_format($count, 0, ',', '.')"
    icon="users"
    :href="route('admin.users.index')"
/>
