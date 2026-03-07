<h1 class="page-title">Dashboard</h1>

<div style="display:grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap:12px;">
    <div class="card">
        <div class="muted" style="font-size:12px;">Projetos</div>
        <div class="strong" style="font-size:22px; margin-top:8px;"><?php echo (int)($projectsTotal ?? 0); ?></div>
    </div>

    <div class="card">
        <div class="muted" style="font-size:12px;">Lotes (total)</div>
        <div class="strong" style="font-size:22px; margin-top:8px;"><?php echo (int)($lotsTotal ?? 0); ?></div>
    </div>

    <div class="card">
        <div class="muted" style="font-size:12px;">Lotes vendidos</div>
        <div class="strong" style="font-size:22px; margin-top:8px;"><?php echo (int)($soldLots ?? 0); ?></div>
    </div>

    <div class="card">
        <div class="muted" style="font-size:12px;">Reservas ativas</div>
        <div class="strong" style="font-size:22px; margin-top:8px;"><?php echo (int)($activeReservations ?? 0); ?></div>
    </div>
</div>

<div style="margin-top:12px; display:grid; grid-template-columns: 1.2fr 0.8fr; gap:12px;">
    <div class="card">
        <div class="strong" style="margin-top:0;">Lotes por status</div>
        <div style="margin-top:10px; display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:10px;">
            <div class="card" style="padding:12px;">
                <div class="muted" style="font-size:12px;">Disponível</div>
                <div class="strong" style="font-size:18px; margin-top:6px;"><?php echo (int)(($lotsByStatus['disponivel'] ?? 0)); ?></div>
            </div>
            <div class="card" style="padding:12px;">
                <div class="muted" style="font-size:12px;">Reservado</div>
                <div class="strong" style="font-size:18px; margin-top:6px;"><?php echo (int)(($lotsByStatus['reservado'] ?? 0)); ?></div>
            </div>
            <div class="card" style="padding:12px;">
                <div class="muted" style="font-size:12px;">Vendido</div>
                <div class="strong" style="font-size:18px; margin-top:6px;"><?php echo (int)(($lotsByStatus['vendido'] ?? 0)); ?></div>
            </div>
            <div class="card" style="padding:12px;">
                <div class="muted" style="font-size:12px;">Indisponível</div>
                <div class="strong" style="font-size:18px; margin-top:6px;"><?php echo (int)(($lotsByStatus['indisponivel'] ?? 0)); ?></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="strong" style="margin-top:0;">Vendas (mês)</div>
        <div style="margin-top:10px;">
            <div class="muted" style="font-size:12px;">Quantidade</div>
            <div class="strong" style="font-size:18px; margin-top:6px;"><?php echo (int)($salesMonthCount ?? 0); ?></div>
        </div>

        <div style="margin-top:10px;">
            <div class="muted" style="font-size:12px;">Total</div>
            <div class="strong" style="font-size:18px; margin-top:6px;">R$ <?php echo number_format((float)($salesMonthTotal ?? 0), 2, ',', '.'); ?></div>
        </div>

        <div style="margin-top:10px;">
            <div class="muted" style="font-size:12px;">Reservas expiradas (hoje)</div>
            <div class="strong" style="font-size:18px; margin-top:6px;"><?php echo (int)($todayExpired ?? 0); ?></div>
        </div>
    </div>
</div>

<div style="margin-top:12px;" class="card">
    <div class="muted">Você está logado como:</div>
    <div class="strong"><?php echo htmlspecialchars((string)$userName, ENT_QUOTES, 'UTF-8'); ?></div>
    <div class="muted">Role: <?php echo htmlspecialchars((string)$userRole, ENT_QUOTES, 'UTF-8'); ?></div>
</div>
