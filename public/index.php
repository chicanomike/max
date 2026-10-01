<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Auth;
use App\Config;
use App\RateLimiter;
use App\Response;
use App\Router;

Response::setSecurityHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$router = new Router();

$router->add('GET', '/', static function (): void {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

    if (str_contains($accept, 'text/html')) {
        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#eef2ef">
    <meta name="description" content="MAX Security coordina prevención, rutas seguras y protección civil para empresas y comunidades.">
    <title>MAX Security | Protección civil y prevención</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #25343a;
            --muted: #5e6d70;
            --paper: #eef2ef;
            --white: #fff;
            --green: #35675d;
            --green-bright: #e5b45e;
            --line: #d4ded9;
            --alert: #b75c42;
            --serif: Georgia, "Times New Roman", serif;
            --sans: "Segoe UI", Tahoma, sans-serif;
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; scroll-padding-top: 88px; }
        body {
            margin: 0;
            color: var(--ink);
            background: var(--paper);
            font-family: var(--sans);
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; }
        a:focus-visible { outline: 3px solid #d88b32; outline-offset: 4px; }
        .container { width: min(1160px, calc(100% - 48px)); margin: 0 auto; }
        .topbar {
            position: sticky;
            top: 0;
            z-index: 5;
            background: rgba(238, 242, 239, .94);
            border-bottom: 1px solid rgba(37, 52, 58, .09);
            backdrop-filter: blur(14px);
        }
        .nav { min-height: 76px; display: flex; align-items: center; justify-content: space-between; gap: 24px; }
        .brand { display: inline-flex; align-items: center; }
        .brand-logo { display: block; width: 74px; height: 54px; object-fit: contain; }
        .nav-links { display: flex; align-items: center; gap: 28px; color: #45534d; font-size: .9rem; }
        .nav-links a { transition: color .2s ease; }
        .nav-links a:hover, .nav-links a[aria-current="page"] { color: var(--green); }
        .nav-contact { padding: 11px 16px; border: 1px solid #b8c5bb; border-radius: 4px; color: var(--ink); font-weight: 650; }
        .hero { padding: 54px 0 72px; }
        .hero-grid { display: grid; grid-template-columns: 1fr .92fr; align-items: center; gap: 58px; }
        .eyebrow { display: flex; align-items: center; gap: 10px; color: var(--green); font-size: .75rem; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; }
        .eyebrow::before { width: 25px; height: 2px; content: ""; background: #8aa93f; }
        h1, h2, h3, p { margin-top: 0; }
        h1 { max-width: 620px; margin: 20px 0; font-family: var(--serif); font-size: clamp(3.25rem, 6.2vw, 5.7rem); font-weight: 400; line-height: 1.02; }
        h1 em { color: var(--green); font-weight: 400; }
        .lead { max-width: 520px; margin-bottom: 27px; color: var(--muted); font-size: 1.05rem; line-height: 1.8; }
        .actions { display: flex; align-items: center; flex-wrap: wrap; gap: 13px; }
        .btn { min-height: 48px; display: inline-flex; align-items: center; justify-content: center; padding: 0 19px; border: 1px solid transparent; border-radius: 4px; font-weight: 700; transition: background-color .2s ease, color .2s ease, transform .2s ease; }
        .btn:hover { transform: translateY(-2px); }
        .btn-primary { background: var(--green); color: white; }
        .btn-primary:hover { background: #0e553a; }
        .btn-secondary { border-color: #c4cec5; color: var(--ink); }
        .hero-visual { min-height: 420px; position: relative; overflow: hidden; background: #18372c; }
        .hero-visual img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: .76; }
        .visual-shade { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(11, 33, 25, .05) 20%, rgba(11, 33, 25, .78) 100%); }
        .visual-label { position: absolute; right: 22px; bottom: 22px; left: 22px; display: flex; align-items: end; justify-content: space-between; gap: 20px; color: white; }
        .visual-label strong { display: block; margin-bottom: 5px; font-family: var(--serif); font-size: 1.7rem; font-weight: 400; }
        .visual-label span { color: #d6e3db; font-size: .83rem; }
        .status { flex: 0 0 auto; display: inline-flex; align-items: center; gap: 8px; padding: 9px 11px; border: 1px solid rgba(255,255,255,.3); border-radius: 3px; background: rgba(17, 43, 32, .55); font-size: .72rem; }
        .status::before { width: 7px; height: 7px; border-radius: 50%; content: ""; background: var(--green-bright); box-shadow: 0 0 0 4px rgba(184,239,112,.15); }
        .trust-band { border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); }
        .trust-inner { min-height: 98px; display: flex; align-items: center; justify-content: space-between; gap: 24px; }
        .trust-inner p { max-width: 270px; margin: 0; color: var(--muted); font-size: .88rem; line-height: 1.55; }
        .trust-points { display: flex; flex-wrap: wrap; gap: 12px 28px; color: var(--ink); font-size: .88rem; font-weight: 650; }
        .trust-points span::before { margin-right: 9px; color: var(--green); content: "✓"; font-weight: 800; }
        .section { padding: 94px 0; }
        .section-heading { max-width: 670px; margin-bottom: 38px; }
        .section-heading h2 { margin: 15px 0 12px; font-family: var(--serif); font-size: clamp(2.3rem, 4vw, 3.4rem); font-weight: 400; line-height: 1.1; }
        .section-heading p { max-width: 570px; margin-bottom: 0; color: var(--muted); line-height: 1.75; }
        .about-grid { display: grid; grid-template-columns: .9fr 1.1fr; align-items: start; gap: 70px; }
        .about-note { padding: 28px 0 0 26px; border-left: 2px solid #9dbb58; color: #46544d; font-family: var(--serif); font-size: 1.45rem; line-height: 1.5; }
        .about-copy { color: var(--muted); line-height: 1.8; }
        .about-copy strong { color: var(--ink); }
        .services-section { background: #e9eee8; }
        .services-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); border-top: 1px solid #cbd5cb; border-left: 1px solid #cbd5cb; }
        .service { min-height: 230px; padding: 26px 25px; border-right: 1px solid #cbd5cb; border-bottom: 1px solid #cbd5cb; transition: background-color .25s ease; }
        .service:hover { background: rgba(255,255,255,.65); }
        .service-number { display: block; margin-bottom: 28px; color: var(--green); font-size: .75rem; font-weight: 800; letter-spacing: .08em; }
        .service h3 { margin-bottom: 10px; font-family: var(--serif); font-size: 1.45rem; font-weight: 400; }
        .service p { margin: 0; color: var(--muted); font-size: .92rem; line-height: 1.7; }
        .process-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 35px; counter-reset: step; }
        .process-step { padding-top: 20px; border-top: 1px solid #cbd5cb; counter-increment: step; }
        .process-step::before { display: block; margin-bottom: 18px; color: var(--green); content: "0" counter(step); font-size: .8rem; font-weight: 800; }
        .process-step h3 { margin-bottom: 9px; font-size: 1rem; }
        .process-step p { margin: 0; color: var(--muted); font-size: .91rem; line-height: 1.7; }
        .contact-section { padding: 0 0 82px; }
        .contact-panel { display: grid; grid-template-columns: 1.1fr .9fr; gap: 45px; padding: 48px; background: #18372c; color: white; }
        .contact-panel .eyebrow { color: var(--green-bright); }
        .contact-panel h2 { max-width: 480px; margin: 15px 0; font-family: var(--serif); font-size: clamp(2.3rem, 4vw, 3.5rem); font-weight: 400; line-height: 1.1; }
        .contact-panel p { max-width: 480px; margin: 0; color: #d0ddd5; line-height: 1.7; }
        .contact-details { display: grid; align-content: center; gap: 20px; }
        .contact-item { padding-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,.2); }
        .contact-item span { display: block; margin-bottom: 5px; color: #b9d1c3; font-size: .77rem; }
        .contact-item a, .contact-item strong { font-size: .98rem; font-weight: 650; }
        .sample-note { color: #d7e7a9 !important; font-size: .75rem; }
        .footer { padding: 22px 0 30px; border-top: 1px solid var(--line); }
        .footer-inner { display: flex; align-items: center; justify-content: space-between; gap: 15px; color: var(--muted); font-size: .8rem; }
        .footer p { margin: 0; }
        @media (prefers-reduced-motion: no-preference) {
            .hero-copy { animation: arrive .65s ease-out both; }
            .hero-visual { animation: arrive .8s .1s ease-out both; }
            @keyframes arrive { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        }
        @media (max-width: 800px) {
            .container { width: min(100% - 36px, 650px); }
            .nav { min-height: 68px; flex-wrap: wrap; gap: 10px; padding: 12px 0; }
            .nav-links { width: 100%; justify-content: space-between; gap: 12px; padding: 3px 0 6px; font-size: .83rem; }
            .nav-contact { padding: 7px 10px; }
            .hero { padding: 42px 0 50px; }
            .hero-grid, .about-grid, .contact-panel { grid-template-columns: 1fr; gap: 30px; }
            .hero-visual { min-height: 330px; }
            .trust-inner { align-items: flex-start; flex-direction: column; justify-content: center; padding: 22px 0; }
            .section { padding: 68px 0; }
            .about-grid { gap: 25px; }
            .about-note { padding-top: 10px; }
            .services-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .process-grid { gap: 22px; }
            .contact-panel { padding: 32px 26px; }
        }
        @media (max-width: 520px) {
            .container { width: calc(100% - 32px); }
            .nav-links { gap: 8px; font-size: .76rem; }
            h1 { font-size: 3.35rem; }
            .lead { font-size: 1rem; }
            .actions { align-items: stretch; flex-direction: column; }
            .btn { width: 100%; }
            .hero-visual { min-height: 290px; }
            .visual-label { right: 15px; bottom: 15px; left: 15px; }
            .status { font-size: 0; }
            .status::before { margin: 0; }
            .trust-points { display: grid; gap: 12px; }
            .services-grid, .process-grid { grid-template-columns: 1fr; }
            .service { min-height: auto; padding: 22px; }
            .service-number { margin-bottom: 18px; }
            .contact-panel { padding: 28px 22px; }
            .footer-inner { align-items: flex-start; flex-direction: column; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
        }
        [hidden] { display: none !important; }
        .stage { min-height: calc(100svh - 76px); display: grid; align-content: center; gap: 24px; padding-top: 22px; padding-bottom: 28px; }
        .hero { width: 100%; padding: 0; }
        .hero-grid { grid-template-columns: 1fr .9fr; gap: 46px; transition: opacity .42s ease, transform .42s cubic-bezier(.22, 1, .36, 1); }
        .hero-grid.is-changing { opacity: 0; transform: translateY(5px); }
        .hero-copy { display: flex; align-items: center; flex-direction: column; text-align: center; }
        .hero-copy .eyebrow { justify-content: center; }
        h1 { max-width: 610px; margin: 17px 0 13px; color: var(--ink); font-size: clamp(3rem, 5.2vw, 4.7rem); text-shadow: 0 1px 0 #fff, 0 3px 0 #d9e1dc, 0 6px 12px rgba(37, 52, 58, .12); }
        h1 em { text-shadow: 0 1px 0 #fff, 0 3px 0 #c7d6cf, 0 6px 12px rgba(37, 52, 58, .13); }
        .lead { max-width: 500px; margin-bottom: 20px; font-size: .98rem; line-height: 1.65; }
        .actions { justify-content: center; }
        .hero-visual { width: 100%; min-height: 0; aspect-ratio: 1.25; }
        .visual-label { right: 18px; bottom: 18px; left: 18px; }
        .visual-label strong { font-size: 1.5rem; }
        .info-explorer { border-top: 0; }
        .view-tabs { display: flex; justify-content: center; gap: 8px; padding-top: 12px; border-bottom: 1px solid var(--line); }
        .view-tab { min-height: 42px; padding: 0 17px; border: 0; border-bottom: 2px solid transparent; background: transparent; color: var(--muted); font: 650 .88rem var(--sans); cursor: pointer; transition: color .2s ease, border-color .2s ease; }
        .view-tab:hover, .view-tab[aria-selected="true"] { border-bottom-color: var(--green); color: var(--green); }
        .view-content { min-height: 176px; padding: 20px 0 0; transition: opacity .42s ease, transform .42s cubic-bezier(.22, 1, .36, 1); }
        .view-content.is-changing { opacity: 0; transform: translateY(12px); }
        .info-panel { text-align: center; }
        .info-panel h2 { margin: 0 0 8px; font-family: var(--serif); font-size: 1.65rem; font-weight: 400; }
        .info-panel > p { max-width: 720px; margin: 0 auto; color: var(--muted); font-size: .92rem; line-height: 1.65; }
        .services-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px 26px; max-width: 900px; margin: 14px auto 0; border: 0; }
        .service { min-height: 0; padding: 8px 10px; border: 0; text-align: left; }
        .service-number { margin-bottom: 4px; font-size: .65rem; }
        .service h3 { margin-bottom: 3px; font-family: var(--sans); font-size: .9rem; font-weight: 700; }
        .service p { font-size: .78rem; line-height: 1.45; }
        .process-grid { max-width: 900px; margin: 15px auto 0; gap: 24px; text-align: left; }
        .process-step { padding-top: 10px; }
        .process-step::before { margin-bottom: 8px; }
        .process-step p { font-size: .82rem; }
        .contact-details { grid-template-columns: repeat(3, 1fr); gap: 18px; max-width: 900px; margin: 17px auto 0; text-align: left; }
        .contact-item { padding: 0 10px 8px; border-bottom: 1px solid var(--line); }
        .contact-item span { color: var(--green); }
        .contact-item a, .contact-item strong { font-size: .9rem; overflow-wrap: anywhere; }
        .sample-note { grid-column: 1 / -1; margin: 0 !important; color: var(--muted) !important; font-size: .72rem; text-align: center; }
        .view-tabs { display: none; }
        .view-content { padding-top: 12px; }
        .map-visual { background: #e4ebe5; }
        .map-visual > svg { position: absolute; inset: 0; width: 100%; height: 100%; }
        .map-road-edge, .map-road, .map-grid-lines { fill: none; stroke-linecap: round; stroke-linejoin: round; }
        .map-road-edge { stroke: #cbd5cf; }
        .map-road { stroke: #fff; }
        .map-grid-lines { stroke: #bccbc0; stroke-width: 2; }
        .map-route { fill: none; stroke: var(--alert); stroke-width: 7; stroke-linecap: round; stroke-linejoin: round; stroke-dasharray: 2 1; }
        .map-legend { position: absolute; top: 16px; left: 16px; display: grid; gap: 7px; padding: 11px 13px; border: 1px solid rgba(37, 52, 58, .12); background: rgba(250, 252, 249, .94); color: var(--ink); font-size: .72rem; box-shadow: 0 5px 16px rgba(37, 52, 58, .08); }
        .map-legend strong { font-size: .78rem; }
        .map-legend span { display: flex; align-items: center; gap: 7px; color: var(--muted); }
        .map-key { width: 8px; height: 8px; border-radius: 50%; background: var(--green); }
        .map-key.alert { background: var(--alert); }
        .map-disclaimer { position: absolute; top: 16px; right: 16px; padding: 7px 9px; border: 1px solid rgba(37, 52, 58, .13); background: rgba(250, 252, 249, .9); color: var(--muted); font-size: .65rem; }
        .map-visual .visual-shade { background: linear-gradient(180deg, transparent 50%, rgba(37, 52, 58, .7) 100%); }
        .map-pin { fill: var(--alert); stroke: #fff; stroke-width: 4; }
        .map-zone { fill: #91af9b; fill-opacity: .32; stroke: #668a74; stroke-width: 2; stroke-dasharray: 7 6; }
        .map-safe-zone { fill: #d8e8d8; fill-opacity: .78; stroke: #628a70; stroke-width: 2; }
        .footer { padding: 12px 0 16px; }
        .footer-inner { font-size: .74rem; }
        .footer .brand-mark { width: 28px; height: 28px; }
        .topbar .nav-links a.is-active { color: var(--green); }
        @media (max-width: 800px) {
            .stage { min-height: calc(100svh - 100px); gap: 20px; padding-top: 22px; }
            .hero { padding: 0; }
            .hero-grid { grid-template-columns: 1fr .9fr; gap: 24px; }
            h1 { font-size: clamp(2.5rem, 6vw, 3.6rem); }
            .hero-visual { min-height: 0; aspect-ratio: .95; }
            .view-content { min-height: 190px; }
            .services-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .process-grid { gap: 16px; }
            .contact-details { gap: 10px; }
        }
        @media (max-width: 520px) {
            .stage { min-height: calc(100svh - 112px); align-content: start; gap: 15px; padding-top: 24px; padding-bottom: 16px; }
            .hero { padding: 0; }
            .hero-grid { grid-template-columns: 1fr; gap: 17px; }
            h1 { max-width: 390px; margin: 12px 0 8px; font-size: clamp(2.65rem, 12vw, 3.35rem); }
            .lead { max-width: 390px; margin-bottom: 12px; font-size: .9rem; line-height: 1.5; }
            .actions { display: none; }
            .hero-visual { width: min(100%, 390px); aspect-ratio: 2.15; margin: 0 auto; }
            .visual-label { right: 12px; bottom: 11px; left: 12px; }
            .visual-label strong { margin-bottom: 2px; font-size: 1.12rem; }
            .visual-label span { font-size: .68rem; }
            .view-tabs { justify-content: space-between; gap: 0; padding-top: 4px; }
            .view-tab { min-height: 40px; padding: 0 8px; font-size: .76rem; }
            .view-content { min-height: 190px; padding-top: 15px; }
            .info-panel h2 { font-size: 1.4rem; }
            .info-panel > p { font-size: .84rem; }
            .services-grid { gap: 5px; margin-top: 10px; }
            .service { padding: 5px; }
            .service h3 { font-size: .82rem; }
            .service p { font-size: .72rem; }
            .process-grid { grid-template-columns: 1fr; gap: 10px; margin-top: 10px; }
            .process-step { display: grid; grid-template-columns: 32px 1fr; column-gap: 8px; padding-top: 7px; }
            .process-step::before { grid-row: span 2; margin: 2px 0 0; }
            .process-step h3 { margin-bottom: 3px; }
            .process-step p { font-size: .76rem; }
            .contact-details { grid-template-columns: 1fr; gap: 9px; margin-top: 12px; }
            .contact-item { padding: 0 0 7px; }
            .contact-item a, .contact-item strong { font-size: .82rem; }
            .sample-note { text-align: left; }
            .footer { padding-top: 9px; }
            .footer-inner { align-items: center; flex-direction: row; }
            .footer-inner p { max-width: 195px; text-align: right; }
        }
        :root { --nav-height: 76px; }
        .scroll-story { position: relative; }
        .stage {
            position: sticky;
            top: var(--nav-height);
            z-index: 2;
            height: calc(100svh - var(--nav-height));
            min-height: 0;
            gap: 18px;
            padding-top: 10px;
            padding-bottom: 10px;
            background: var(--paper);
        }
        .hero-visual { aspect-ratio: 1.45; }
        .scroll-markers { position: relative; z-index: 0; margin-top: calc(-1 * (100svh - var(--nav-height))); }
        .scroll-marker { height: 100svh; }
        @media (max-width: 800px) {
            :root { --nav-height: 100px; }
            .stage { height: calc(100svh - var(--nav-height)); gap: 14px; padding-block: 10px; }
            .hero-visual { aspect-ratio: 1.3; }
        }
        @media (max-width: 520px) {
            .stage { gap: 10px; padding-block: 12px; }
            .hero-visual { aspect-ratio: 2.15; }
            .scroll-marker { height: 92svh; }
        }
        @media (max-width: 520px) and (max-height: 760px) {
            .stage { gap: 6px; padding-block: 6px; }
            .hero-grid { gap: 7px; }
            h1 { margin: 7px 0 5px; font-size: 2.4rem; }
            .lead { margin-bottom: 6px; font-size: .82rem; }
            .hero-visual { aspect-ratio: 3.1; }
            .view-tabs { padding-top: 0; }
            .view-tab { min-height: 34px; }
            .view-content { min-height: 0; padding-top: 7px; }
            .info-panel h2 { margin-bottom: 4px; font-size: 1.2rem; }
            .services-grid { margin-top: 5px; }
            .service p { font-size: .68rem; }
            .scroll-marker { height: 70svh; }
        }
        @media (max-width: 520px) and (max-height: 900px) {
            .stage { grid-template-rows: auto minmax(0, 1fr); gap: 6px; padding-block: 6px; }
            .hero-grid { gap: 8px; }
            h1 { margin: 8px 0 6px; font-size: 2.45rem; }
            .lead { margin-bottom: 6px; font-size: .8rem; line-height: 1.4; }
            .hero-visual { aspect-ratio: 2.8; }
            .visual-label { bottom: 8px; }
            .info-explorer { display: flex; min-height: 0; flex-direction: column; }
            .view-content { min-height: 0; flex: 1; overflow-y: auto; overscroll-behavior-y: auto; padding: 6px 5px 14px 0; scrollbar-color: #a8b8ae transparent; scrollbar-width: thin; }
            .view-content::-webkit-scrollbar { width: 4px; }
            .view-content::-webkit-scrollbar-thumb { border-radius: 4px; background: #a8b8ae; }
            .info-panel h2 { margin-bottom: 5px; font-size: 1.22rem; }
            .info-panel > p { font-size: .76rem; line-height: 1.45; }
            .services-grid { margin-top: 5px; }
            .service p { font-size: .68rem; line-height: 1.3; }
            .process-grid { gap: 7px; margin-top: 5px; }
            .process-step { padding-top: 5px; }
            .process-step p { font-size: .7rem; line-height: 1.35; }
            .contact-details { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px 10px; margin-top: 7px; }
            .contact-item { min-width: 0; padding: 0 0 5px; }
            .contact-item span { margin-bottom: 3px; font-size: .68rem; }
            .contact-item a, .contact-item strong { display: block; font-size: .72rem; line-height: 1.35; overflow-wrap: anywhere; }
            .sample-note { font-size: .65rem; }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="container nav">
            <a class="brand" href="#inicio" aria-label="MAX Security, inicio"><img class="brand-logo" src="max-logo.png" alt="MAX Security"></a>
            <nav class="nav-links" aria-label="Navegación principal">
                <a href="#contenido" data-view="empresa" aria-current="page">La empresa</a>
                <a href="#contenido" data-view="servicios">Servicios</a>
                <a href="#contenido" data-view="metodo">Cómo trabajamos</a>
                <a class="nav-contact" href="#contenido" data-view="contacto">Contacto</a>
            </nav>
        </div>
    </header>

        <main class="scroll-story" id="inicio">
            <div class="container stage">
        <section class="hero" aria-label="Presentación de MAX Security">
            <div class="hero-grid">
                <div class="hero-copy">
                        <div class="eyebrow">Protección civil · prevención · comunidad</div>
                        <h1>Preparados hoy. <em>Protegidos siempre.</em></h1>
                        <p class="lead">Planes, rutas y coordinación para cuidar a las personas y mantener la operación segura.</p>
                </div>
                    <template id="map-visual-template">
                        <div class="hero-visual map-visual" role="img" aria-label="Mapa ilustrativo de zonas seguras, rutas de evacuación y punto de encuentro">
                            <svg viewBox="0 0 900 620" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                                <rect width="900" height="620" fill="#e7eee8"/>
                                <path d="M0 86 160 38 294 74 402 30 548 78 702 32 900 90V0H0ZM0 520 116 466 260 502 398 452 558 510 704 458 900 506V620H0Z" fill="#d9e4d9"/>
                                <path d="M62 116 210 96 258 188 180 258 66 226ZM320 92 454 72 492 172 402 224 300 180ZM592 92 744 66 826 142 774 228 630 198ZM216 322 334 264 420 326 386 438 266 456 184 392ZM484 270 600 234 694 296 654 412 526 438 452 358ZM726 320 836 266 900 312 900 444 802 468 712 408Z" fill="#d1ddd2" stroke="#c3d1c6" stroke-width="3"/>
                                <path class="map-road-edge" stroke-width="26" d="M-30 284C148 246 206 298 334 262S580 214 930 250M120-30C160 132 124 242 200 348S284 520 248 660M530-20C486 136 566 230 520 342S494 512 548 650M766-30C712 120 816 202 754 322S748 500 826 650M-20 492C162 438 250 498 382 470S662 446 924 484"/>
                                <path class="map-road" stroke-width="20" d="M-30 284C148 246 206 298 334 262S580 214 930 250M120-30C160 132 124 242 200 348S284 520 248 660M530-20C486 136 566 230 520 342S494 512 548 650M766-30C712 120 816 202 754 322S748 500 826 650M-20 492C162 438 250 498 382 470S662 446 924 484"/>
                                <path class="map-grid-lines" d="m36 150 128-30m132 82 142-70m118 12 148-36M42 400l118-42m230 42 44-108m76 180 124-48m-548 96 86-34m416-180 126-60"/>
                                <path class="map-zone" d="M300 188 416 142 488 208 458 328 350 354 274 286Z"/>
                                <path class="map-safe-zone" d="M550 338 624 302 686 344 674 414 590 430 540 384Z"/>
                                <path class="map-route" d="M338 250C390 270 414 302 460 300S530 346 606 370"/>
                                <circle cx="338" cy="250" r="13" class="map-pin"/>
                                <circle cx="606" cy="370" r="17" fill="#35675d" stroke="#fff" stroke-width="5"/>
                                <circle cx="606" cy="370" r="5" fill="#fff"/>
                                <g fill="#54676a" font-family="Segoe UI, sans-serif" font-size="15" font-weight="600">
                                    <text x="368" y="232">ZONA DE ATENCIÓN</text>
                                    <text x="620" y="399">PUNTO SEGURO</text>
                                    <text x="63" y="334">RUTA DE EVACUACIÓN</text>
                                </g>
                            </svg>
                            <div class="map-legend"><strong>Mapa de prevención</strong><span><i class="map-key alert"></i>Zona de atención</span><span><i class="map-key"></i>Punto de encuentro</span></div>
                            <span class="map-disclaimer">Mapa ilustrativo</span>
                            <div class="visual-shade"></div>
                            <div class="visual-label"><div><strong>Personas primero.</strong><span>Prevención · rutas · respuesta</span></div><span class="status">Plan de protección activo</span></div>
                        </div>
                    </template>
            </div>
        </section>

        <section class="info-explorer" id="contenido" aria-label="Información de MAX Security">
            <div class="view-content" aria-live="polite">
                <section class="info-panel" id="panel-empresa" data-panel="empresa">
                    <template data-hero>
                        <div class="hero-copy"><div class="eyebrow">Nuestra organización</div><h1>Protección cercana. <em>Comunidad preparada.</em></h1><p class="lead">Diseñamos prevención y respuesta pensando primero en las personas.</p></div>
                    </template>
                    <h2>Protección civil para empresas y comunidades.</h2>
                    <p>MAX Security ayuda a las organizaciones a prepararse antes de una emergencia y a responder con claridad cuando cada minuto importa. Convertimos riesgos, instalaciones y procedimientos en planes que las personas pueden entender y poner en práctica.</p>
                    <p>Trabajamos en prevención, preparación y coordinación: identificamos áreas de atención, definimos rutas y puntos de encuentro, y ayudamos a que cada integrante conozca su función. El objetivo es proteger vidas y mantener una respuesta ordenada.</p>
                </section>
                <section class="info-panel" id="panel-servicios" data-panel="servicios" hidden>
                    <template data-hero>
                        <div class="hero-copy"><div class="eyebrow">Servicios de protección civil</div><h1>Riesgos visibles. <em>Respuestas claras.</em></h1><p class="lead">Mapas, protocolos y capacitación para actuar con orden ante una emergencia.</p></div>
                    </template>
                    <h2>Prevención práctica para proteger vidas.</h2>
                    <div class="services-grid">
                        <article class="service"><span class="service-number">01 / DIAGNÓSTICO</span><h3>Mapa de riesgos</h3><p>Identificamos amenazas, zonas vulnerables y recursos disponibles dentro de tus instalaciones.</p></article>
                        <article class="service"><span class="service-number">02 / PREPARACIÓN</span><h3>Planes de emergencia</h3><p>Definimos responsables, procedimientos y contactos para distintos escenarios.</p></article>
                        <article class="service"><span class="service-number">03 / EVACUACIÓN</span><h3>Rutas y señalización</h3><p>Organizamos salidas, zonas seguras y puntos de encuentro fáciles de identificar.</p></article>
                        <article class="service"><span class="service-number">04 / CAPACITACIÓN</span><h3>Brigadas y simulacros</h3><p>Practicamos evacuación, primeros auxilios y comunicación para que cada persona sepa actuar.</p></article>
                        <article class="service"><span class="service-number">05 / COORDINACIÓN</span><h3>Protocolos de respuesta</h3><p>Establecemos cómo alertar, atender a las personas y coordinar apoyos durante un incidente.</p></article>
                        <article class="service"><span class="service-number">06 / CONTINUIDAD</span><h3>Revisión y mejora</h3><p>Actualizamos planes, mapas y recursos después de simulacros, cambios o incidentes.</p></article>
                    </div>
                </section>
                <section class="info-panel" id="panel-metodo" data-panel="metodo" hidden>
                    <template data-hero>
                        <div class="hero-copy"><div class="eyebrow">Cómo trabajamos</div><h1>Un plan compartido. <em>Una respuesta coordinada.</em></h1><p class="lead">Acompañamos a tu equipo desde el mapa inicial hasta cada simulacro.</p></div>
                    </template>
                    <h2>De conocer el lugar a saber cómo actuar.</h2>
                    <div class="process-grid">
                        <article class="process-step"><h3>1. Recorremos</h3><p>Conocemos tus espacios, actividad, aforo y necesidades de las personas que los ocupan.</p></article>
                        <article class="process-step"><h3>2. Trazamos</h3><p>Señalamos riesgos, rutas de salida, puntos de reunión y recursos de emergencia en un plan claro.</p></article>
                        <article class="process-step"><h3>3. Practicamos</h3><p>Capacitamos responsables, realizamos simulacros y ajustamos el plan con lo aprendido.</p></article>
                    </div>
                </section>
                <section class="info-panel" id="panel-contacto" data-panel="contacto" hidden>
                    <template data-hero>
                        <div class="hero-copy"><div class="eyebrow">Contacto directo</div><h1>Hablemos de prevención. <em>Cuenta con nosotros.</em></h1><p class="lead">Cuéntanos sobre tu espacio y las personas que necesitas proteger.</p></div>
                    </template>
                    <h2>Empecemos por conocer tus necesidades.</h2>
                    <p>Escríbenos o llámanos con el tipo de espacio, el número aproximado de personas y tus dudas. Coordinaremos una primera conversación para orientar los siguientes pasos.</p>
                    <div class="contact-details">
                        <div class="contact-item"><span>Correo</span><a href="mailto:hola@maxsecurity.example">hola@maxsecurity.example</a></div>
                        <div class="contact-item"><span>Teléfono</span><a href="tel:+15550142026">+1 (555) 014-2026</a></div>
                        <div class="contact-item"><span>Oficina</span><strong>Av. Central 245, Ciudad Ejemplo</strong></div>
                        <div class="contact-item"><span>Horario de ejemplo</span><strong>Lunes a viernes, 8:00 a 18:00</strong></div>
                        <div class="contact-item"><span>Horario de ejemplo</span><strong>Lunes a viernes, 8:00 a 18:00</strong></div>
                        <p class="sample-note">Datos de contacto ficticios para esta demostración.</p>
                    </div>
                </section>
            </div>
        </section>
            </div>
            <div class="scroll-markers" aria-hidden="true">
                <div class="scroll-marker" data-scroll-view="empresa"></div>
                <div class="scroll-marker" data-scroll-view="servicios"></div>
                <div class="scroll-marker" data-scroll-view="metodo"></div>
                <div class="scroll-marker" data-scroll-view="contacto"></div>
            </div>
        </main>

    <script>
        const viewTabs = Array.from(document.querySelectorAll('[data-view]'));
        const viewPanels = Array.from(document.querySelectorAll('[data-panel]'));
        const scrollModules = Array.from(document.querySelectorAll('[data-scroll-view]'));
        const viewContent = document.querySelector('.view-content');
        const heroGrid = document.querySelector('.hero-grid');
        const mapTemplate = document.querySelector('#map-visual-template');
        let currentView = 'empresa';
        let viewChangeTimer;

        heroGrid.append(mapTemplate.content.firstElementChild.cloneNode(true));

        function activateView(view) {
            const activePanel = viewPanels.find((panel) => panel.dataset.panel === view);
            if (!activePanel || view === currentView) return;

            currentView = view;
            window.clearTimeout(viewChangeTimer);
            viewContent.scrollTop = 0;
            viewContent.classList.add('is-changing');
            heroGrid.classList.add('is-changing');
            viewChangeTimer = window.setTimeout(() => {
                const heroTemplate = activePanel.querySelector('[data-hero]');
                if (heroTemplate) {
                    heroGrid.replaceChildren(heroTemplate.content.cloneNode(true), mapTemplate.content.firstElementChild.cloneNode(true));
                }
                viewPanels.forEach((panel) => {
                    panel.hidden = panel.dataset.panel !== view;
                });
                viewTabs.forEach((tab) => {
                    const isActive = tab.dataset.view === view;
                    if (isActive) tab.setAttribute('aria-current', 'page');
                    else tab.removeAttribute('aria-current');
                });
                requestAnimationFrame(() => {
                    viewContent.classList.remove('is-changing');
                    heroGrid.classList.remove('is-changing');
                });
            }, 180);
        }

        viewTabs.forEach((tab) => {
            tab.addEventListener('click', (event) => {
                event.preventDefault();
                activateView(tab.dataset.view);
                const marker = scrollModules.find((item) => item.dataset.scrollView === tab.dataset.view);
                marker?.scrollIntoView({
                    behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                    block: 'center',
                });
            });

        });

        if ('IntersectionObserver' in window) {
            let moduleObserver;
            const observeScrollModules = () => {
                moduleObserver?.disconnect();
                const observerMargin = Math.round(window.innerHeight * 0.4);
                moduleObserver = new IntersectionObserver((entries) => {
                    const activeModule = entries
                        .filter((entry) => entry.isIntersecting)
                        .sort((first, second) => second.intersectionRatio - first.intersectionRatio)[0];

                    if (activeModule) activateView(activeModule.target.dataset.scrollView);
                }, { rootMargin: `-${observerMargin}px 0px -${observerMargin}px 0px`, threshold: 0 });

                scrollModules.forEach((module) => moduleObserver.observe(module));
            };

            observeScrollModules();
            window.addEventListener('resize', observeScrollModules, { passive: true });
        }
    </script>
</body>
</html>
HTML;
        Response::html($html);
    }

    Response::json([
        'ok' => true,
        'application' => Config::get('APP_NAME', 'MAX Security API'),
        'message' => 'API funcionando correctamente.',
        'timestamp' => gmdate('c'),
        'environment' => Config::get('APP_ENV', 'development'),
        'endpoints' => [
            'GET /health',
            'POST /login',
            'GET /profile',
            'GET /posts',
        ],
    ]);
});

$router->add('GET', '/health', static function (): void {
    Response::json([
        'ok' => true,
        'application' => Config::get('APP_NAME', 'MAX Security API'),
        'timestamp' => gmdate('c'),
        'environment' => Config::get('APP_ENV', 'development'),
    ]);
});

$router->add('POST', '/login', static function (): void {
    if (!RateLimiter::allow('login:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), (int) Config::get('RATE_LIMIT_PER_MINUTE', 60), 60)) {
        Response::json(['error' => 'Demasiadas peticiones. Inténtalo más tarde.'], 429);
        return;
    }

    $input = json_decode((string) file_get_contents('php://input'), true);
    $email = trim((string) ($input['email'] ?? ''));
    $password = (string) ($input['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        Response::json(['error' => 'Credenciales inválidas.'], 400);
        return;
    }

    $user = Auth::authenticate($email, $password);

    if ($user === null) {
        Response::json(['error' => 'Credenciales incorrectas.'], 401);
        return;
    }

    $token = Auth::issueToken((string) $user['id'], (string) $user['email']);

    Response::json([
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
        ],
    ]);
});

$router->add('GET', '/profile', static function (): void {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $payload = Auth::verifyToken($authHeader);

    if ($payload === null) {
        Response::json(['error' => 'Token inválido o expirado.'], 401);
        return;
    }

    Response::json([
        'user' => [
            'id' => $payload['sub'],
            'email' => $payload['email'],
        ],
        'expires_at' => $payload['exp'],
    ]);
});

$router->add('GET', '/posts', static function (): void {
    Response::json([
        [
            'id' => 1,
            'title' => 'Buenas prácticas de seguridad web',
            'content' => 'Usa HTTPS, valida inputs, aplica CSP y protege credenciales.',
            'published_at' => '2026-09-28',
        ],
        [
            'id' => 2,
            'title' => 'Autenticación con JWT',
            'content' => 'Usa tokens con expiración y secretos fuertes.',
            'published_at' => '2026-09-28',
        ],
    ]);
});

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
