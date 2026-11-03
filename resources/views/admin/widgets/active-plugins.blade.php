<x-admin.stat-card
    data-stat="plugins-ativos"
    title="Plugins ativos"
    description="Carregados na aplicação"
    :value="number_format($count, 0, ',', '.')"
    icon="cog-6-tooth"
    tone="emerald"
    :href="route('settings').'#plugins'"
/>
