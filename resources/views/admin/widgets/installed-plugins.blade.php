<x-admin.stat-card
    data-stat="plugins-instalados"
    title="Plugins instalados"
    description="Extensões disponíveis"
    :value="number_format($count, 0, ',', '.')"
    icon="squares-2x2"
    :href="route('settings').'#plugins'"
/>
