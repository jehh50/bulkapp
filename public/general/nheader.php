<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php echo security_csrf_meta(); ?>
    <script src="<?php echo asset('public/js/csrf.js'); ?>"></script>
  <title><?php echo defined('APP_TITLE') ? APP_TITLE : 'Bulksales'; ?></title>
  <link href="public/images/icon.png" rel="icon" type="image/png"/>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="<?php echo asset('public/css/bootstrap.min.css'); ?>" rel="stylesheet">
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/4.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
  <style>
    :root{
      --blue-900:#12306b; --blue-700:#1a4391; --blue-600:#2d57a8; --blue-500:#2f5fc4;
      --gold:#f5b83d; --app-bg:#f4f6fa; --ink:#252525; --muted:#c9d6f0; --line:#eef0f4;
      --purple:#6f42c1;
    }
    body{background:var(--app-bg);font-family:Poppins,system-ui,sans-serif;color:var(--ink)}

    /* ---------- Navbar ---------- */
    .app-nav{background:var(--blue-900);min-height:56px;padding-top:0;padding-bottom:0;z-index:30}
    .app-nav .navbar-brand{display:flex;align-items:center;gap:10px;color:#fff;font-weight:700;font-size:18px}
    .brand-logo{width:28px;height:28px;border-radius:8px;background:var(--gold);color:var(--blue-900);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:700}
    .app-nav .nav-link{color:var(--muted);font-weight:500}
    .app-nav .nav-link:hover,.app-nav .nav-link:focus,.app-nav .nav-link.show{color:#fff}
    .app-nav .nav-link.active{color:#fff}
    @media (min-width:992px){
      .app-nav .nav-item{display:flex;align-items:center}
      .app-nav .navbar-nav .nav-link{height:56px;display:flex;align-items:center}
      .app-nav .nav-link.active{box-shadow:inset 0 -3px 0 var(--gold)}
    }
    .app-nav .dropdown-menu{--bs-dropdown-min-width:240px;--bs-dropdown-border-radius:0 0 12px 12px;--bs-dropdown-link-active-bg:rgba(245,184,61,.22);--bs-dropdown-link-active-color:var(--ink);box-shadow:0 12px 24px rgba(0,0,0,.25)}
    .app-nav .dropdown-item{border-radius:8px;padding:10px 14px}
    .app-nav .dropdown-item:hover{background:rgba(245,184,61,.25)}
    .app-nav .dropdown-item.active,.app-nav .dropdown-item:active{font-weight:600}

    /* ---------- Hero ---------- */
    .hero{background:var(--blue-700);color:#fff;padding:36px 0 90px}
    .hero__in{max-width:1280px;margin:0 auto;padding:0 32px;display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:16px}
    .hero__crumb{font-size:13px;color:var(--muted);margin-bottom:6px}
    .hero__title{margin:0;font-size:30px;font-weight:600;line-height:1.2}
    .hero__accent{color:var(--gold);font-weight:700}
    .hero__sub{font-size:14px;color:var(--muted);margin-top:6px}
    .hero__card{display:flex;align-items:center;gap:12px;background:var(--blue-900);border-radius:14px;padding:12px 18px}
    .hero__dot{width:10px;height:10px;border-radius:50%;background:var(--gold)}
    .hero__lbl{font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:var(--muted)}
    .hero__val{font-size:22px;font-weight:700;line-height:1.2}
    .hero__val--sm{font-size:15px;font-weight:500}
    .hero__sep{width:1px;align-self:stretch;background:var(--blue-600);margin:0 6px}

    /* ---------- Main ---------- */
    .app-main{max-width:1280px;margin:-56px auto 40px;padding:0 24px}

    /* ---------- Filtros ---------- */
    .filter-card{background:#fff;border:0;border-radius:16px;box-shadow:0 8px 24px rgba(18,48,107,.12);padding:20px 24px;margin-bottom:20px}
    .filter-label{font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--ink)}
    .filter-card .form-control,.filter-card .form-select{height:42px;border-radius:10px}
    .btn-primary{--bs-btn-bg:var(--blue-700);--bs-btn-border-color:var(--blue-700);--bs-btn-hover-bg:var(--blue-900);--bs-btn-hover-border-color:var(--blue-900);--bs-btn-active-bg:var(--blue-900);--bs-btn-active-border-color:var(--blue-900)}
    .pagination{--bs-pagination-color:var(--blue-700);--bs-pagination-hover-color:var(--blue-900);--bs-pagination-active-bg:var(--blue-700);--bs-pagination-active-border-color:var(--blue-700);--bs-pagination-focus-box-shadow:0 0 0 .2rem rgba(47,95,196,.25)}
    .btn-gold{--bs-btn-bg:var(--gold);--bs-btn-border-color:var(--gold);--bs-btn-color:var(--ink);--bs-btn-hover-bg:#e9a924;--bs-btn-hover-border-color:#e9a924;--bs-btn-hover-color:var(--ink);--bs-btn-active-bg:#e9a924;--bs-btn-active-border-color:#e9a924;--bs-btn-active-color:var(--ink)}
    .search-btn{height:42px;border-radius:10px;font-weight:600}

    /* ---------- Tabla ---------- */
    .data-card{background:#fff;border:0;border-radius:16px;box-shadow:0 1px 3px rgba(16,24,40,.08);overflow:hidden;margin-bottom:20px}
    .data-table{margin-bottom:0;font-size:14px}
    .data-table th,.data-table td{text-align:right;padding:12px;vertical-align:middle}
    .data-table th:first-child,.data-table td:first-child{text-align:left;padding-left:24px}
    .data-table thead th{background:#f1f4fa;color:var(--ink);font-size:11.5px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;border-bottom:0;white-space:nowrap}
    .data-table tbody td{border-top:1px solid var(--line);border-bottom:0}
    .data-table tbody td:first-child{font-weight:500}
    .data-table tfoot td{background:var(--blue-900)!important;color:#fff;font-weight:600;padding:14px 12px;border:0}
    .data-table tfoot td:first-child{padding-left:24px}
    .data-empty{text-align:center!important;color:#6b7280;padding:40px 24px!important}
    .data-table td.ta-left,.data-table th.ta-left{text-align:left}
    .table-sticky thead th{position:sticky;top:0;z-index:1;box-shadow:0 1px 0 #dfe5f2}

    /* ---------- KPIs ---------- */
    .kpi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:20px}
    .kpi{background:#fff;border-radius:16px;padding:18px 20px;box-shadow:0 1px 3px rgba(16,24,40,.08);border-top:4px solid var(--blue-700)}
    .kpi__label{font-size:12px;font-weight:600;color:var(--ink);text-transform:uppercase;letter-spacing:.5px}
    .kpi__row{display:flex;align-items:baseline;gap:10px;margin-top:6px}
    .kpi__value{font-size:34px;font-weight:700;line-height:1}
    .kpi__sub{font-size:13px;font-weight:500;color:var(--ink)}

    /* ---------- Gráficas ---------- */
    .charts-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,420px),1fr));gap:16px;margin-bottom:20px}
    .chart-card{background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(16,24,40,.08);padding:20px 24px}
    .chart-card__head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px}
    .chart-card__title{font-weight:600;font-size:16px}
    .chart-body{display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:24px}
    .donut{width:168px;height:168px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex:none}
    .donut__hole{width:102px;height:102px;border-radius:50%;background:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center}
    .donut__label{font-size:11px;color:var(--ink);text-transform:uppercase}
    .donut__total{font-size:24px;font-weight:700;line-height:1.1}
    .donut--lg{width:220px;height:220px}
    .donut--lg .donut__hole{width:136px;height:136px}
    .donut--lg .donut__total{font-size:32px}
    .legend{display:flex;flex-direction:column;gap:8px;flex:1;min-width:180px}
    .legend__item{display:flex;align-items:center;gap:10px;font-size:13px}
    .legend__sw{width:10px;height:10px;border-radius:3px;flex:none}
    .legend__label{flex:1}
    .legend__n{font-weight:600}
    .legend__pct{color:var(--ink);width:52px;text-align:right}

    /* ---------- Tabla: operador / píldoras / barra ---------- */
    .op{display:flex;align-items:center;gap:12px}
    .op__avatar{width:34px;height:34px;border-radius:50%;color:var(--ink);font-size:12px;font-weight:600;display:flex;align-items:center;justify-content:center;flex:none}
    .op__name{font-weight:500}
    .badge-top{font-size:11px;font-weight:600;background:#fdf0cf;color:#8a5a00;border-radius:20px;padding:2px 8px;white-space:nowrap}
    .pill{display:inline-block;min-width:28px;text-align:center;padding:2px 10px;border-radius:20px;font-weight:600}
    .pill--wide{min-width:52px}
    .bar{display:flex;height:8px;border-radius:4px;overflow:hidden;background:#d5dcec}
    .bar__fill{background:var(--blue-700)}
    .bar-meta{display:flex;justify-content:space-between;font-size:11.5px;color:var(--ink);margin-top:4px}
    .data-table tbody tr.is-zero > td{background:#fff5f5}

    /* ---------- Cards por operador (móvil) ---------- */
    .cards-title{font-weight:600;font-size:16px;padding:4px 4px 0;margin-bottom:12px}
    .op-cards{display:flex;flex-direction:column;gap:12px;margin-bottom:20px}
    .op-card{background:#fff;border-radius:16px;padding:16px;box-shadow:0 1px 3px rgba(16,24,40,.08)}
    .op-card.is-zero{background:#fff5f5}
    .op-card__head{display:flex;align-items:center;gap:12px;margin-bottom:12px}
    .op-card__id{flex:1;min-width:0}
    .op-card__name{font-weight:600;font-size:14px}
    .op-card__grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;text-align:center;margin-bottom:12px}
    .op-card__n{font-size:18px;font-weight:600}
    .op-card__l{font-size:10.5px;color:var(--ink);text-transform:uppercase}
    .op-cards__totals{background:var(--blue-900);color:#fff;border-radius:16px;padding:16px;display:flex;justify-content:space-between;align-items:center;font-weight:600;gap:12px}

    @media (max-width:991px){
      .hero{padding:24px 0 80px}
      .hero__in{padding:0 16px}
      .hero__title{font-size:24px}
      .app-main{padding:0 16px}
      .filter-card{padding:16px}
      .kpi-grid{grid-template-columns:repeat(auto-fit,minmax(150px,1fr))}
      .kpi__value{font-size:28px}
    }
  </style>
</head>
<body>
