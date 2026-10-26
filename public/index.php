<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Auth;
use App\AdminController;
use App\Config;
use App\RateLimiter;
use App\Response;
use App\Router;
use App\Session;
use App\SubmissionController;

Session::start();
date_default_timezone_set((string) Config::get('APP_TIMEZONE', 'America/Mexico_City'));
Response::setSecurityHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$router = new Router();
(new AdminController())->register($router);
SubmissionController::register($router);

$router->add('GET', '/', static function (): void {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

    if (str_contains($accept, 'text/html')) {
        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1d506b">
    <meta name="description" content="MAX Asesoría Empresarial Especializada. Asesoría, programas internos, capacitación y dictámenes técnicos.">
    <title>MAX | Asesoría Empresarial Especializada</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #27343c;
            --muted: #626d72;
            --paper: #eef2ef;
            --white: #fff;
            --green: #1d506b;
            --green-bright: #edc33f;
            --line: #d4ded9;
            --alert: #c3443b;
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
        .brand-lockup { display: inline-flex; align-items: center; gap: 11px; }
        .brand-lockup strong { color: var(--green); font-size: 1.25rem; font-weight: 800; }
        .brand-lockup span { max-width: 165px; color: #a77b56; font-size: .59rem; font-weight: 800; line-height: 1.3; }
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
        .hero-visual { min-height: 420px; position: relative; overflow: hidden; background: #1d4052; }
        .hero-visual img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: .9; }
        .visual-shade { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(14, 38, 53, .02) 20%, rgba(14, 38, 53, .78) 100%); }
        .visual-label { position: absolute; right: 22px; bottom: 22px; left: 22px; display: flex; align-items: end; justify-content: space-between; gap: 20px; color: white; }
        .visual-label strong { display: block; margin-bottom: 5px; font-family: var(--serif); font-size: 1.7rem; font-weight: 400; }
        .visual-label span { color: #d6e3db; font-size: .83rem; }
        .status { flex: 0 0 auto; display: inline-flex; align-items: center; gap: 8px; padding: 9px 11px; border: 1px solid rgba(255,255,255,.3); border-radius: 3px; background: rgba(14, 38, 53, .62); font-size: .72rem; }
        .status::before { width: 7px; height: 7px; border-radius: 50%; content: ""; background: var(--green-bright); box-shadow: 0 0 0 4px rgba(237,195,63,.2); }
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
        .stage { grid-template-rows: auto minmax(0, 1fr); overflow: hidden; }
        .info-explorer { display: flex; min-height: 0; flex-direction: column; }
        .view-content { min-height: 0; flex: 1; overflow-y: auto; overscroll-behavior: contain; scrollbar-color: #a8b8ae transparent; scrollbar-width: thin; }
        .proof-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; max-width: 700px; margin: 16px auto 0; }
        .proof-item { padding: 12px 10px; border: 1px solid var(--line); border-radius: 7px; background: rgba(255,255,255,.58); text-align: left; }
        .proof-item strong { display: block; margin-bottom: 4px; font-family: var(--serif); font-size: 1rem; font-weight: 400; }
        .proof-item span { display: block; color: var(--muted); font-size: .68rem; line-height: 1.4; }
        .mission-note { max-width: 560px; margin: 12px auto 0 !important; padding: 10px 14px; border: 1px solid var(--line); border-radius: 6px; background: rgba(255,255,255,.6); color: var(--green) !important; font-family: var(--serif); font-size: 1.15rem !important; }
        .services-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px 12px; max-width: 1000px; margin-top: 12px; }
        .service { padding: 10px; border: 1px solid var(--line); border-radius: 7px; background: rgba(255,255,255,.55); }
        .service-number { margin-bottom: 5px; }
        .service h3 { font-size: .82rem; line-height: 1.25; }
        .service p { font-size: .7rem; line-height: 1.4; }
        .process-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; max-width: 1000px; }
        .process-step { min-height: 110px; padding: 12px; border: 1px solid var(--line); border-radius: 7px; background: rgba(255,255,255,.58); }
        .process-step::before { margin-bottom: 8px; }
        .process-step h3 { font-size: .86rem; }
        .process-step p { font-size: .72rem; line-height: 1.45; }
        .contact-panel { display: grid; grid-template-columns: .85fr 1.15fr; gap: 18px; max-width: 1000px; margin: 0 auto; padding: 18px; border-radius: 8px; background: #1d4052; color: white; text-align: left; }
        .contact-intro h3 { margin: 9px 0; font-family: var(--serif); font-size: 1.55rem; font-weight: 400; line-height: 1.12; }
        .contact-intro p { color: #d0ddd5; font-size: .78rem; line-height: 1.5; }
        .contact-checks { display: grid; gap: 7px; margin: 12px 0 0; color: #e2eee6; font-size: .69rem; }
        .contact-checks a { color: #fff; font-weight: 700; overflow-wrap: anywhere; }
        .contact-layout { min-width: 0; }
        .lead-form { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; padding: 12px; border-radius: 7px; background: #fff; color: var(--ink); }
        .lead-form label { display: grid; gap: 4px; color: var(--muted); font-size: .64rem; font-weight: 700; }
        .lead-form label.full { grid-column: 1 / -1; }
        .lead-form input, .lead-form select, .lead-form textarea { width: 100%; min-width: 0; min-height: 32px; padding: 7px 8px; border: 1px solid var(--line); border-radius: 4px; background: white; color: var(--ink); font: 400 .72rem var(--sans); }
        .lead-form textarea { min-height: 48px; resize: vertical; }
        .lead-form button { min-height: 34px; grid-column: 1 / -1; border: 0; border-radius: 4px; background: var(--green); color: white; font: 700 .72rem var(--sans); cursor: pointer; }
        .lead-form button:disabled { opacity: .65; cursor: wait; }
        .form-feedback { grid-column: 1 / -1; margin: 0; padding: 8px; border-left: 3px solid var(--green); background: #edf4ef; color: var(--ink); font-size: .72rem; line-height: 1.45; }
        .form-feedback.is-error { border-color: var(--alert); background: #fff0ee; }
        .contact-layout .sample-note { margin: 7px 0 0 !important; color: #d7e7a9 !important; font-size: .64rem; text-align: left; }
        .compact-footer { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; max-width: 1000px; margin: 12px auto 0; padding-top: 10px; border-top: 1px solid var(--line); color: var(--muted); font-size: .66rem; text-align: left; }
        .compact-footer p { margin: 0; }
        .compact-footer nav { display: flex; justify-content: end; flex-wrap: wrap; gap: 12px; }
        .compact-footer a:hover { color: var(--green); }
        @media (max-width: 800px) {
            .services-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .process-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .contact-panel { grid-template-columns: 1fr 1.2fr; }
        }
        @media (max-width: 520px) {
            .proof-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; margin-top: 9px; }
            .proof-item { padding: 8px; }
            .proof-item strong { font-size: .88rem; }
            .proof-item span { font-size: .62rem; }
            .services-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 5px; }
            .service { padding: 7px; }
            .service h3 { font-size: .76rem; }
            .service p { font-size: .65rem; }
            .process-grid { grid-template-columns: 1fr 1fr; }
            .process-step { min-height: 0; padding: 8px; }
            .contact-panel { grid-template-columns: 1fr; gap: 10px; padding: 12px; }
            .contact-intro h3 { font-size: 1.3rem; }
            .contact-intro p { margin-bottom: 5px; }
            .contact-checks { grid-template-columns: 1fr 1fr; gap: 5px; margin-top: 7px; font-size: .62rem; }
            .lead-form { gap: 6px; padding: 9px; }
            .compact-footer { grid-template-columns: 1fr; }
            .compact-footer nav { justify-content: start; }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="container nav">
            <a class="brand" href="#inicio" aria-label="MAX Asesoría Empresarial Especializada, inicio"><span class="brand-lockup"><strong>MAX</strong><span>ASESORÍA EMPRESARIAL<br>ESPECIALIZADA</span></span></a>
            <nav class="nav-links" aria-label="Navegación principal">
                <a href="#contenido" data-view="empresa" aria-current="page">Quiénes somos</a>
                <a href="#contenido" data-view="servicios">Servicios</a>
                <a href="#contenido" data-view="metodo">Metodología</a>
                <a class="nav-contact" href="#contenido" data-view="contacto">Solicitar asesoría</a>
            </nav>
        </div>
    </header>

        <main class="scroll-story" id="inicio">
            <div class="container stage">
        <section class="hero" aria-label="Presentación de MAX">
            <div class="hero-grid">
                <div class="hero-copy">
                        <div class="eyebrow">Asesoría · capacitación · dictámenes</div>
                        <h1>Asesoría empresarial <em>especializada</em></h1>
                        <p class="lead">Soluciones de seguridad y prevención con sustento en la normatividad aplicable.</p>
                </div>
                    <template id="hero-visual-template">
                        <div class="hero-visual">
                            <img src="" alt="" loading="eager" decoding="async">
                            <div class="visual-shade" aria-hidden="true"></div>
                            <div class="visual-label"><div><strong data-visual-title></strong><span data-visual-description></span></div><span class="status" data-visual-status></span></div>
                        </div>
                    </template>
            </div>
        </section>

        <section class="info-explorer" id="contenido" aria-label="Información de MAX">
            <div class="view-content" aria-live="polite">
                <section class="info-panel" id="panel-empresa" data-panel="empresa">
                    <template data-hero>
                        <div class="hero-copy"><div class="eyebrow">Quiénes somos</div><h1>Asesoría empresarial <em>especializada.</em></h1><p class="lead">Prevención y cumplimiento con criterios técnicos claros.</p></div>
                    </template>
                    <h2>Quiénes somos</h2>
                    <p>MAX brinda asesoría empresarial especializada en prevención, protección civil y gestión de riesgos. Acompañamos a empresas e instituciones con programas, capacitación y dictámenes técnicos.</p>
                    <p>Nuestro trabajo se desarrolla con criterios técnicos y documentación sustentada en las normas y disposiciones aplicables, para ofrecer orientación clara y profesional.</p>
                    <div class="proof-grid" aria-label="Misión y visión de MAX">
                        <div class="proof-item"><strong>Misión</strong><span>Ayudar a las organizaciones a prevenir riesgos y proteger a las personas mediante asesoría especializada, capacitación y soluciones responsables.</span></div>
                        <div class="proof-item"><strong>Visión</strong><span>Ser una firma confiable y reconocida por su profesionalismo, claridad técnica y compromiso con la seguridad.</span></div>
                    </div>
                </section>
                <section class="info-panel" id="panel-servicios" data-panel="servicios" hidden>
                    <template data-hero>
                        <div class="hero-copy"><div class="eyebrow">Servicios especializados</div><h1>Prevención con respaldo <em>técnico.</em></h1><p class="lead">Asesoría, formación y dictámenes para empresas e instituciones.</p></div>
                    </template>
                    <h2>Servicios especializados</h2>
                    <div class="services-grid">
                        <article class="service"><span class="service-number">01 / ASESORÍA</span><h3>Asesorías especializadas</h3><p>Orientación técnica para atender necesidades de prevención y seguridad empresarial.</p></article>
                        <article class="service"><span class="service-number">02 / PROTECCIÓN CIVIL</span><h3>Programas internos</h3><p>Elaboración y actualización de programas internos de protección civil.</p></article>
                        <article class="service"><span class="service-number">03 / ANÁLISIS</span><h3>Análisis de riesgo</h3><p>Identificación de peligros y evaluación de riesgos para orientar medidas preventivas.</p></article>
                        <article class="service"><span class="service-number">04 / DICTÁMENES</span><h3>Dictámenes técnicos</h3><p>Dictámenes de riesgo, eléctricos, de gas, estructurales y de gas LP.</p></article>
                        <article class="service"><span class="service-number">05 / CAPACITACIÓN</span><h3>Primeros auxilios</h3><p>Capacitación para responder ante emergencias y brindar atención inicial.</p></article>
                        <article class="service"><span class="service-number">06 / PREVENCIÓN</span><h3>Incendios y extintores</h3><p>Combate de incendios, uso y manejo de extintores.</p></article>
                        <article class="service"><span class="service-number">07 / FORMACIÓN</span><h3>Manejo de ambulancia</h3><p>Curso de manejo de ambulancia con certificación.</p></article>
                        <article class="service"><span class="service-number">08 / PERITAJE</span><h3>Firma pericial</h3><p>Servicios de firma pericial para documentación y dictámenes técnicos.</p></article>
                    </div>
                </section>
                <section class="info-panel" id="panel-metodo" data-panel="metodo" hidden>
                    <template data-hero>
                        <div class="hero-copy"><div class="eyebrow">Metodología de trabajo</div><h1>Prevención clara. <em>Respuesta coordinada.</em></h1><p class="lead">Acompañamiento profesional para fortalecer la seguridad.</p></div>
                    </template>
                    <h2>Etapas de trabajo</h2>
                    <div class="process-grid">
                        <article class="process-step"><h3>Diagnóstico</h3><p>Revisamos instalaciones, actividades y condiciones de riesgo.</p></article>
                        <article class="process-step"><h3>Fundamento técnico</h3><p>Identificamos normas y disposiciones aplicables al servicio solicitado.</p></article>
                        <article class="process-step"><h3>Propuesta y documentación</h3><p>Preparamos medidas y documentos con criterios técnicos claros.</p></article>
                        <article class="process-step"><h3>Acompañamiento</h3><p>Orientamos sobre la atención de requisitos y siguientes pasos.</p></article>
                    </div>
                </section>
                <section class="info-panel" id="panel-contacto" data-panel="contacto" hidden>
                    <template data-hero>
                        <div class="hero-copy"><div class="eyebrow">Asesoría profesional</div><h1>Solicita una <em>asesoría.</em></h1><p class="lead">Conoce soluciones de protección civil para tu organización.</p></div>
                    </template>
                    <div class="contact-panel">
                        <div class="contact-intro">
                            <div class="eyebrow">Asesoría especializada</div>
                            <h3>Conversemos sobre lo que necesita tu empresa.</h3>
                            <p>Contacta a MAX para recibir información sobre asesorías, programas, capacitación y dictámenes.</p>
                            <div class="contact-checks"><a href="tel:+529612333442">961 233 3442</a><a href="mailto:programas.internos.max@gmail.com">programas.internos.max@gmail.com</a></div>
                        </div>
                        <div class="contact-layout">
                            <form class="lead-form" action="solicitudes" method="post" data-request-form>
                                <input type="hidden" name="csrf_token" value="__CSRF_TOKEN__">
                                <input type="hidden" name="submission_key" value="__SUBMISSION_KEY__">
                                <label>Nombre completo<input name="nombre" autocomplete="name" maxlength="150" required></label>
                                <label>Correo electrónico<input name="correo" type="email" autocomplete="email" maxlength="254" required></label>
                                <label>Teléfono<input name="telefono" type="tel" autocomplete="tel" minlength="7" maxlength="30" pattern="[0-9+(). -]{7,30}" title="Escribe entre 7 y 15 dígitos; puedes incluir espacios y signos." required></label>
                                <label>Servicio de interés<select name="servicio" required><option value="" selected disabled>Selecciona un servicio</option><option value="Asesoría especializada">Asesoría especializada</option><option value="Programa interno">Programa interno</option><option value="Análisis de riesgo">Análisis de riesgo</option><option value="Dictamen técnico">Dictamen técnico</option><option value="Capacitación">Capacitación</option><option value="Firma pericial">Firma pericial</option><option value="Otro servicio">Otro servicio</option></select></label>
                                <label class="full">¿Cómo podemos ayudarte?<textarea name="mensaje" rows="2" maxlength="3000" required></textarea></label>
                                <button type="submit" data-submit-button>Solicitar asesoría</button>
                                <p class="form-feedback" role="status" aria-live="polite" hidden></p>
                            </form>
                        </div>
                    </div>
                    <footer class="compact-footer">
                        <p><strong>MAX</strong> · Asesoría Empresarial Especializada<br>Prevención, seguridad y confianza.</p>
                        <nav aria-label="Navegación del pie de página">
                            <a href="#inicio">Inicio</a>
                            <a href="#contenido" data-view="empresa">Quiénes somos</a>
                            <a href="#contenido" data-view="servicios">Servicios</a>
                            <a href="#contenido" data-view="metodo">Metodología</a>
                            <a href="#contenido" data-view="contacto">Solicitar asesoría</a>
                        </nav>
                    </footer>
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
        const requestForm = document.querySelector('[data-request-form]');
        requestForm?.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!requestForm.reportValidity()) return;

            const submitButton = requestForm.querySelector('[data-submit-button]');
            const feedback = requestForm.querySelector('.form-feedback');
            const previousLabel = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.textContent = 'Enviando...';
            feedback.hidden = true;
            feedback.classList.remove('is-error');

            try {
                const response = await fetch(requestForm.action, {
                    method: 'POST',
                    body: new FormData(requestForm),
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.error || 'No fue posible enviar la solicitud.');

                requestForm.reset();
                requestForm.elements.submission_key.value = result.submission_key;
                feedback.textContent = result.whatsapp_status === 'enviado'
                    ? `Solicitud #${String(result.id).padStart(3, '0')} registrada. La notificación fue enviada.`
                    : `Solicitud #${String(result.id).padStart(3, '0')} registrada. La notificación de WhatsApp está pendiente y podrá reintentarse desde administración.`;
                feedback.hidden = false;
            } catch (error) {
                feedback.textContent = error.message || 'No fue posible enviar la solicitud. Inténtalo de nuevo.';
                feedback.classList.add('is-error');
                feedback.hidden = false;
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = previousLabel;
            }
        });

        const viewTabs = Array.from(document.querySelectorAll('[data-view]'));
        const viewPanels = Array.from(document.querySelectorAll('[data-panel]'));
        const scrollModules = Array.from(document.querySelectorAll('[data-scroll-view]'));
        const viewContent = document.querySelector('.view-content');
        const heroGrid = document.querySelector('.hero-grid');
        const visualTemplate = document.querySelector('#hero-visual-template');
        const visualContent = {
            empresa: {
                image: 'safety-advisory.jpg',
                alt: 'Profesionales con equipo de protección conversan durante una revisión en obra',
                title: 'Seguridad desde el diagnóstico.',
                description: 'Asesoría · programas · prevención',
                status: 'Protección civil empresarial',
            },
            servicios: {
                image: 'fire-drill-training.jpg',
                alt: 'Bomberos realizan un ejercicio de extinción de fuego con mangueras',
                title: 'Capacitación en respuesta.',
                description: 'Brigadas · incendios · extintores',
                status: 'Formación en campo',
            },
            metodo: {
                image: 'electrical-safety-check.jpg',
                alt: 'Técnico con chaleco de protección durante una revisión de seguridad eléctrica',
                title: 'Cada riesgo se revisa en sitio.',
                description: 'Instalaciones · documentación · seguimiento',
                status: 'Evaluación técnica',
            },
            contacto: {
                image: 'emergency-ambulance.jpg',
                alt: 'Ambulancia de emergencia estacionada en la calle',
                title: 'Atención preparada.',
                description: 'Capacitación · respuesta · seguridad',
                status: 'Manejo de ambulancia',
            },
        };
        let currentView = 'empresa';
        let viewChangeTimer;
        let navigationTarget = null;
        let navigationTimer;

        function createHeroVisual(view) {
            const visual = visualTemplate.content.firstElementChild.cloneNode(true);
            const content = visualContent[view];
            const image = visual.querySelector('img');

            image.src = content.image;
            image.alt = content.alt;
            visual.querySelector('[data-visual-title]').textContent = content.title;
            visual.querySelector('[data-visual-description]').textContent = content.description;
            visual.querySelector('[data-visual-status]').textContent = content.status;

            return visual;
        }

        heroGrid.append(createHeroVisual('empresa'));

        function completeNavigation() {
            if (navigationTarget === null) return;

            const target = navigationTarget;
            navigationTarget = null;
            window.clearTimeout(navigationTimer);
            activateView(target);
        }

        document.addEventListener('scrollend', (event) => {
            if (event.target === document) completeNavigation();
        }, { passive: true });
        document.addEventListener('scroll', () => {
            if (navigationTarget === null) return;

            window.clearTimeout(navigationTimer);
            navigationTimer = window.setTimeout(completeNavigation, 180);
        }, { passive: true });

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
                    heroGrid.replaceChildren(heroTemplate.content.cloneNode(true), createHeroVisual(view));
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
                const target = tab.dataset.view;
                navigationTarget = target;
                window.clearTimeout(navigationTimer);
                activateView(target);
                const marker = scrollModules.find((item) => item.dataset.scrollView === target);
                marker?.scrollIntoView({
                    behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                    block: 'center',
                });
                navigationTimer = window.setTimeout(completeNavigation, 800);
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

                    if (activeModule && navigationTarget === null) {
                        activateView(activeModule.target.dataset.scrollView);
                    }
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
        $html = str_replace(
            ['__CSRF_TOKEN__', '__SUBMISSION_KEY__'],
            [htmlspecialchars(Session::csrfToken(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), bin2hex(random_bytes(32))],
            $html
        );
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

    if (strlen((string) Config::get('JWT_SECRET', '')) < 32) {
        Response::json(['error' => 'La autenticación no está configurada.'], 503);
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
