<?php
/**
 * Ana site ile uyumlu public sayfa stilleri (kurallar, gizlilik vb.).
 * @var int $brandHomeSize
 */
$brandHomeSize = isset($brandHomeSize) ? (int) $brandHomeSize : 48;
?>
<style>
  :root{
    --obsidian:#0b0906; --obsidian-2:#161009; --obsidian-3:#1f160d;
    --blood:#8f1c29; --blood-light:#c53347;
    --gold:#c9974a; --gold-light:#eccd8e;
    --jade:#33594a; --jade-light:#4f8a71;
    --parchment:#e9dfc6; --ash:#9a8f80;
    --line:rgba(201,151,74,.15);
    --font-display:'Cinzel',serif;
    --font-brush:'Ma Shan Zheng',cursive;
    --font-body:'Inter',sans-serif;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  html{scroll-behavior:smooth;}
  body{
    font-family:var(--font-body);
    background:var(--obsidian);
    color:var(--parchment);
    min-height:100vh;
    line-height:1.55;
    position:relative;
    overflow-x:hidden;
  }
  body::before{
    content:"";
    position:fixed; inset:0;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.05'/%3E%3C/svg%3E");
    pointer-events:none; z-index:1000; mix-blend-mode:overlay;
  }
  body::after{
    content:"";
    position:fixed; inset:0;
    background:
      radial-gradient(ellipse 70% 50% at 15% -10%, rgba(143,28,41,.22), transparent 55%),
      radial-gradient(ellipse 55% 45% at 90% 10%, rgba(201,151,74,.12), transparent 50%),
      radial-gradient(ellipse 50% 40% at 50% 100%, rgba(51,89,74,.1), transparent 55%);
    pointer-events:none; z-index:0;
  }
  ::selection{background:var(--blood); color:var(--gold-light);}
  a{color:var(--gold-light); text-decoration:none;}
  a:hover{color:var(--gold);}
  img{max-width:100%; display:block;}

  .site-top{
    position:sticky; top:0; z-index:50;
    padding:16px 0;
    background:rgba(11,9,6,.88);
    border-bottom:1px solid var(--line);
    backdrop-filter:blur(10px);
  }
  .site-top .inner{
    max-width:1180px; margin:0 auto; padding:0 24px;
    display:flex; align-items:center; justify-content:space-between; gap:16px;
  }
  .site-top .brand-logo{height:<?= max(28, (int) round($brandHomeSize * 0.75)) ?>px; width:auto;}
  .site-top .back{
    display:inline-flex; align-items:center; gap:8px;
    font-size:.82rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase;
    color:var(--ash); border:1px solid var(--line); padding:8px 14px;
    clip-path:polygon(6px 0,100% 0,100% calc(100% - 6px),calc(100% - 6px) 100%,0 100%,0 6px);
    transition:color .25s, border-color .25s, background .25s;
  }
  .site-top .back:hover{color:var(--gold-light); border-color:var(--gold); background:rgba(201,151,74,.06);}

  .page-shell{position:relative; z-index:1;}
  .wrap{max-width:1180px; margin:0 auto; padding:48px 24px 72px;}

  .hero{margin-bottom:32px; max-width:720px;}
  .hero .eyebrow{
    font-family:var(--font-brush);
    font-size:1.35rem;
    color:var(--blood-light);
    letter-spacing:.05em;
    display:inline-flex; align-items:center; gap:.6rem;
    margin-bottom:.55rem;
  }
  .hero .eyebrow::before, .hero .eyebrow::after{
    content:""; width:28px; height:1px;
    background:linear-gradient(90deg, transparent, var(--gold));
  }
  .hero .eyebrow::after{transform:scaleX(-1);}
  .hero h1{
    font-family:var(--font-display);
    font-size:clamp(1.7rem, 3.2vw, 2.35rem);
    color:var(--gold-light);
    font-weight:700;
    letter-spacing:.02em;
  }
  .hero p{color:var(--ash); margin-top:12px; font-size:.95rem; line-height:1.7;}

  .panel{
    border:1px solid var(--line);
    background:
      linear-gradient(165deg, rgba(31,22,13,.92), rgba(22,16,9,.96) 55%, rgba(11,9,6,.98));
    box-shadow:0 24px 60px rgba(0,0,0,.35);
    position:relative;
  }
  .panel::before{
    content:"";
    position:absolute; inset:0;
    background:linear-gradient(90deg, transparent, rgba(201,151,74,.08), transparent);
    height:1px; top:0; pointer-events:none;
  }

  /* Rules table */
  .rules-table-wrap{overflow-x:auto;}
  table.rules{width:100%; border-collapse:collapse; min-width:900px;}
  table.rules th, table.rules td{
    padding:16px 14px; border-bottom:1px solid rgba(201,151,74,.1); vertical-align:top;
  }
  table.rules th{
    font-size:.7rem; letter-spacing:.1em; text-transform:uppercase;
    color:var(--gold); background:rgba(143,28,41,.16);
    white-space:nowrap; font-weight:700;
  }
  table.rules td{font-size:.88rem; color:#d8cbb8;}
  table.rules tbody tr:hover td{background:rgba(201,151,74,.03);}
  table.rules tr:last-child td{border-bottom:0;}
  table.rules .no{
    width:48px; text-align:center; vertical-align:middle;
    color:var(--gold-light); font-family:var(--font-display); font-weight:700; font-size:1.05rem;
  }
  table.rules th.no{text-align:center;}
  table.rules .title{width:20%; font-weight:600; color:var(--parchment); vertical-align:middle;}
  table.rules .detail{white-space:pre-line; color:var(--ash); font-size:.82rem; line-height:1.65;}
  table.rules th.pen, table.rules td.pen{
    width:13%; text-align:center; vertical-align:middle;
    font-size:.8rem; color:#e0c9a0; line-height:1.45;
  }

  /* Legal / privacy prose */
  .legal-body{padding:28px 32px 36px; font-size:.92rem; color:#d8cbb8; line-height:1.75;}
  .legal-body h2{
    font-family:var(--font-display);
    font-size:1.05rem;
    color:var(--gold-light);
    letter-spacing:.06em;
    text-transform:uppercase;
    margin:28px 0 12px;
    padding-bottom:8px;
    border-bottom:1px solid rgba(201,151,74,.18);
  }
  .legal-body h2:first-child{margin-top:0;}
  .legal-body h3{
    font-family:var(--font-display);
    font-size:.95rem;
    color:var(--parchment);
    margin:20px 0 8px;
  }
  .legal-body p{margin:0 0 14px;}
  .legal-body ul, .legal-body ol{margin:0 0 14px 1.2em;}
  .legal-body li{margin-bottom:6px;}
  .legal-body strong{color:var(--parchment);}
  .legal-body .closing{
    margin-top:28px; padding:16px 18px;
    border:1px solid rgba(143,28,41,.35);
    background:rgba(143,28,41,.12);
    color:var(--gold-light);
    font-size:.88rem;
  }

  .empty{padding:48px 24px; text-align:center; color:var(--ash);}
  .page-foot{
    margin-top:28px; display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap;
    color:var(--ash); font-size:.82rem;
  }

  footer.site-foot{
    position:relative; z-index:1;
    border-top:1px solid rgba(201,151,74,.12);
    padding:48px 24px 32px;
    background:var(--obsidian-2);
  }
  .foot-inner{
    max-width:1180px; margin:0 auto;
    display:flex; justify-content:space-between; gap:40px; flex-wrap:wrap;
  }
  .foot-col h4{
    font-size:.75rem; letter-spacing:.1em; text-transform:uppercase;
    color:var(--gold-light); margin-bottom:14px;
  }
  .foot-col a{display:block; color:var(--ash); margin:8px 0; font-size:.88rem; transition:color .25s;}
  .foot-col a:hover{color:var(--gold-light);}
  .foot-brand p{color:var(--ash); font-size:.85rem; max-width:28ch; line-height:1.65;}
  .foot-brand .copy{font-size:.75rem; margin-top:12px;}

  @media (max-width:720px){
    .legal-body{padding:22px 18px 28px;}
    .wrap{padding:36px 16px 56px;}
  }
</style>
