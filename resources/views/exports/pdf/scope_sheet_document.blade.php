<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scope Sheet — AquaShield CRM — {{ $scopeSheet->claimNumber ?? $scopeSheet->claimInternalId }}</title>
</head>
<style>
    /* ══════════════════════════════════════════════════════
       AQUASHIELD CRM — SCOPE SHEET PDF — Enterprise 2026
       Brand: Primary #00B5E2 | Navy #0C2340
    ══════════════════════════════════════════════════════ */

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: Arial, Helvetica, sans-serif;
        color: #1A202C;
        background: #ffffff;
        font-size: 11px;
        line-height: 1.5;
    }

    @page {
        size: A4;
        margin: 20px 18px 48px 18px;
    }

    /* ── Watermark ── */
    .aquashield-watermark {
        position: fixed;
        top: 38%;
        left: -10%;
        right: -10%;
        text-align: center;
        font-size: 68px;
        font-weight: bold;
        color: rgba(0, 181, 226, 0.045);
        transform: rotate(-35deg);
        pointer-events: none;
        letter-spacing: 8px;
        z-index: 0;
    }

    /* ── Fixed page info strip (top-right) ── */
    .fixed-names {
        position: fixed;
        top: -10px;
        left: 0;
        right: 0;
        height: 22px;
        background: #0C2340;
        padding: 4px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 7.5px;
        color: rgba(255, 255, 255, 0.70);
        letter-spacing: 0.4px;
        text-transform: uppercase;
    }

    .fixed-names .doc-ref {
        font-weight: bold;
        color: #00B5E2;
    }

    .page-number::before {
        content: counter(page);
    }

    /* ── Header logo bar ── */
    .header-logo-wrap {
        width: 100%;
        background: linear-gradient(120deg, #0C2340 0%, #0e2f55 55%, #00B5E2 100%);
        padding: 0;
        position: relative;
        overflow: hidden;
    }

    .header-logo-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 20px;
        position: relative;
        z-index: 2;
    }

    .header-logo-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .header-logo-wrap img {
        height: 42px;
        width: auto;
        object-fit: contain;
        filter: brightness(0) invert(1);
    }

    .header-brand-block {
        border-left: 2px solid rgba(0, 181, 226, 0.6);
        padding-left: 12px;
    }

    .header-brand-block .brand-name {
        font-size: 16px;
        font-weight: bold;
        color: #ffffff;
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    .header-brand-block .brand-tagline {
        font-size: 8px;
        color: rgba(255, 255, 255, 0.65);
        letter-spacing: 1.5px;
        text-transform: uppercase;
        margin-top: 2px;
    }

    .header-badge {
        background: rgba(0, 181, 226, 0.18);
        border: 1px solid rgba(0, 181, 226, 0.5);
        border-radius: 3px;
        padding: 6px 12px;
        text-align: right;
    }

    .header-badge .badge-label {
        font-size: 7px;
        color: rgba(255, 255, 255, 0.60);
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .header-badge .badge-value {
        font-size: 11px;
        font-weight: bold;
        color: #00B5E2;
        letter-spacing: 0.5px;
    }

    /* Geometric accent lines inside header */
    .header-accent-line {
        position: absolute;
        right: 120px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: rgba(0, 181, 226, 0.25);
        z-index: 1;
    }

    .header-accent-line-2 {
        position: absolute;
        right: 128px;
        top: 0;
        bottom: 0;
        width: 1px;
        background: rgba(0, 181, 226, 0.12);
        z-index: 1;
    }

    /* ── Document title block ── */
    .doc-title-block {
        margin-top: 0;
        padding: 18px 20px 14px 20px;
        border-left: 5px solid #00B5E2;
        background: linear-gradient(90deg, #E8F7FC 0%, #f0fbff 60%, #ffffff 100%);
        margin-bottom: 0;
    }

    .doc-title-block .doc-main-title {
        font-size: 20px;
        font-weight: bold;
        color: #0C2340;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        margin: 0 0 4px 0;
    }

    .doc-title-block .doc-sub-title {
        font-size: 9px;
        color: #00B5E2;
        letter-spacing: 2px;
        text-transform: uppercase;
        font-weight: bold;
    }

    .doc-title-divider {
        height: 3px;
        background: linear-gradient(90deg, #00B5E2 0%, #0C2340 60%, transparent 100%);
        margin: 0;
    }

    /* ── Section heading ── */
    .section-heading {
        display: table;
        width: 100%;
        margin: 18px 0 10px 0;
    }

    .section-heading-bar {
        background: linear-gradient(90deg, #0C2340 0%, #0e3060 70%, #1a4a80 100%);
        padding: 7px 14px 7px 16px;
        display: table;
        width: 100%;
    }

    .section-heading-bar .sh-dot {
        display: inline-block;
        width: 6px;
        height: 6px;
        background: #00B5E2;
        border-radius: 50%;
        margin-right: 8px;
        vertical-align: middle;
    }

    .section-heading-bar .sh-text {
        font-size: 10px;
        font-weight: bold;
        color: #ffffff;
        letter-spacing: 2px;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .section-heading-bar .sh-accent-line {
        float: right;
        display: inline-block;
        width: 30px;
        height: 2px;
        background: #00B5E2;
        margin-top: 6px;
    }

    .section-sub-bar {
        height: 2px;
        background: linear-gradient(90deg, #00B5E2 0%, rgba(0,181,226,0.2) 60%, transparent 100%);
    }

    /* ── Info table (main data) ── */
    .info-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0 0 16px 0;
    }

    .info-table tr:nth-child(odd) td {
        background: #F7FBFF;
    }

    .info-table tr:nth-child(even) td {
        background: #ffffff;
    }

    .info-table tr td {
        border-bottom: 1px solid #E2EBF3;
        padding: 6px 10px;
        vertical-align: middle;
    }

    .info-table td.label {
        width: 38%;
        font-size: 9.5px;
        font-weight: bold;
        color: #4A5568;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-left: 4px solid #00B5E2;
        padding-left: 12px;
    }

    .info-table td.value {
        font-size: 11px;
        font-weight: bold;
        color: #0C2340;
    }

    /* ── Meta stats row ── */
    .meta-stats-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 6px;
        margin: 12px 0 10px 0;
    }

    .meta-stat-cell {
        background: linear-gradient(135deg, #0C2340 0%, #0e3060 100%);
        border-radius: 3px;
        padding: 8px 12px;
        text-align: center;
        vertical-align: middle;
        width: 25%;
    }

    .meta-stat-cell .stat-value {
        font-size: 20px;
        font-weight: bold;
        color: #00B5E2;
        display: block;
        line-height: 1.1;
    }

    .meta-stat-cell .stat-label {
        font-size: 7px;
        color: rgba(255, 255, 255, 0.65);
        text-transform: uppercase;
        letter-spacing: 1px;
        display: block;
        margin-top: 2px;
    }

    /* ── Cover image ── */
    .cover-image-wrap {
        margin: 12px 0 0 0;
        position: relative;
        border: 2px solid #00B5E2;
        border-radius: 3px;
        overflow: hidden;
    }

    .cover-image {
        width: 100%;
        height: auto;
        display: block;
        max-height: 340px;
        object-fit: cover;
    }

    .cover-caption-bar {
        background: linear-gradient(90deg, #0C2340 0%, #0e3060 100%);
        padding: 5px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .cover-caption-bar .cap-label {
        font-size: 8px;
        color: #00B5E2;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: bold;
    }

    .cover-caption-bar .cap-ref {
        font-size: 8px;
        color: rgba(255, 255, 255, 0.55);
        letter-spacing: 0.5px;
    }

    /* ── Page break ── */
    .page-break {
        page-break-before: always;
    }

    /* ── Photo grid ── */
    .photo-grid-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 8px;
        margin-top: 8px;
    }

    .photo-card {
        border: 1px solid #CBD5E0;
        border-radius: 3px;
        overflow: hidden;
        background: #F7FBFF;
        padding: 0;
        vertical-align: top;
    }

    .photo-card img {
        width: 100%;
        height: 240px;
        object-fit: cover;
        display: block;
    }

    .photo-card-caption {
        background: #0C2340;
        padding: 4px 8px;
        font-size: 7.5px;
        color: rgba(255, 255, 255, 0.75);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        text-align: center;
    }

    /* ── Zone header banner ── */
    .zone-banner {
        background: linear-gradient(90deg, #0C2340 0%, #0e3060 70%, #1a4a80 100%);
        padding: 10px 18px;
        margin: 0 0 8px 0;
        display: table;
        width: 100%;
        border-left: 5px solid #00B5E2;
    }

    .zone-banner .zone-banner-title {
        font-size: 13px;
        font-weight: bold;
        color: #ffffff;
        letter-spacing: 1.5px;
        text-transform: uppercase;
    }

    .zone-banner .zone-banner-sub {
        font-size: 8px;
        color: rgba(0, 181, 226, 0.85);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 2px;
    }

    /* ── Zone notes ── */
    .zone-notes-block {
        border-left: 4px solid #00B5E2;
        background: #F0F9FF;
        padding: 8px 12px;
        margin-top: 10px;
        border-radius: 0 3px 3px 0;
    }

    .zone-notes-block .notes-label {
        font-size: 8px;
        font-weight: bold;
        color: #00B5E2;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 3px;
    }

    .zone-notes-block .notes-text {
        font-size: 10.5px;
        color: #0C2340;
        line-height: 1.6;
    }

    /* ── Page sub-label (multi-page zones) ── */
    .page-sub-label {
        font-size: 8px;
        color: #00B5E2;
        font-weight: bold;
        letter-spacing: 1px;
        text-transform: uppercase;
        padding: 3px 0 6px 2px;
    }

    /* ── Signature page ── */
    .signature-section-intro {
        background: linear-gradient(90deg, #E8F7FC 0%, #f0fbff 100%);
        border-left: 5px solid #00B5E2;
        padding: 10px 16px;
        margin: 4px 0 18px 0;
        border-radius: 0 3px 3px 0;
    }

    .signature-section-intro p {
        margin: 0;
        font-size: 10px;
        color: #4A5568;
        line-height: 1.6;
    }

    .signature-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 12px;
        margin-top: 10px;
    }

    .signature-card {
        border: 1px solid #CBD5E0;
        border-radius: 4px;
        padding: 20px 18px 14px 18px;
        text-align: center;
        vertical-align: top;
        width: 50%;
        background: #FAFCFF;
        border-top: 4px solid #00B5E2;
    }

    .signature-spacer {
        height: 54px;
    }

    .signature-line {
        border: none;
        border-top: 2px dashed #CBD5E0;
        width: 80%;
        margin: 0 auto 10px auto;
    }

    .signature-name {
        font-size: 13px;
        font-weight: bold;
        color: #0C2340;
        letter-spacing: 0.5px;
    }

    .signature-role {
        font-size: 9px;
        color: #00B5E2;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-top: 3px;
    }

    .signature-date {
        font-size: 9px;
        color: #718096;
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px solid #E2EBF3;
    }

    /* ── Footer ── */
    .footer {
        position: fixed;
        bottom: -30px;
        left: 0;
        right: 0;
        width: 100%;
    }

    .footer-accent {
        height: 3px;
        background: linear-gradient(90deg, #00B5E2 0%, #0C2340 100%);
    }

    .footer-bar {
        background: #0C2340;
        padding: 5px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .footer-bar .footer-brand {
        font-size: 7.5px;
        color: rgba(255, 255, 255, 0.60);
        letter-spacing: 0.5px;
    }

    .footer-bar .footer-brand strong {
        color: #00B5E2;
        font-weight: bold;
    }

    .footer-bar .footer-center {
        font-size: 7px;
        color: rgba(255, 255, 255, 0.35);
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .footer-bar .footer-page {
        font-size: 7.5px;
        color: rgba(255, 255, 255, 0.60);
    }

    /* ── Utility ── */
    .mt-4  { margin-top: 4px; }
    .mt-8  { margin-top: 8px; }
    .mt-12 { margin-top: 12px; }
    .mt-16 { margin-top: 16px; }
    .mb-8  { margin-bottom: 8px; }

    .text-cyan   { color: #00B5E2; }
    .text-navy   { color: #0C2340; }
    .text-muted  { color: #718096; }

    table { border-spacing: 0; }
</style>

<body>

    {{-- ── Diagonal watermark ── --}}
    <div class="aquashield-watermark">AQUASHIELD</div>

    {{-- ── Fixed page-info strip (top) ── --}}
    <div class="fixed-names">
        <span>
            <span class="doc-ref">AQUASHIELD CRM</span>
            &nbsp;·&nbsp; Scope Sheet &nbsp;·&nbsp;
            {{ $scopeSheet->claimNumber ?? $scopeSheet->claimInternalId }}
        </span>
        <span>Page <span class="page-number"></span> &nbsp;·&nbsp; Confidential</span>
    </div>

    {{-- ════════════════════════════════════════════
         PAGE 1 — Cover / Info
    ════════════════════════════════════════════ --}}

    {{-- Header logo bar --}}
    <div class="header-logo-wrap">
        <div class="header-accent-line"></div>
        <div class="header-accent-line-2"></div>
        <div class="header-logo-inner">
            <div class="header-logo-left">
                <img src="{{ $logoBase64 }}" alt="AquaShield Logo">
                <div class="header-brand-block">
                    <div class="brand-name">AquaShield</div>
                    <div class="brand-tagline">Restoration &amp; Claims Management</div>
                </div>
            </div>
            <div class="header-badge">
                <div class="badge-label">Document Ref.</div>
                <div class="badge-value">{{ $scopeSheet->claimNumber ?? $scopeSheet->claimInternalId }}</div>
                <div class="badge-label" style="margin-top:3px;">{{ $generatedAt }}</div>
            </div>
        </div>
    </div>

    {{-- Document title block --}}
    <div class="doc-title-block">
        <div class="doc-main-title">Scope Sheet</div>
        <div class="doc-sub-title">AquaShield CRM &nbsp;·&nbsp; Property Damage Assessment Report</div>
    </div>
    <div class="doc-title-divider"></div>

    {{-- Quick-stats bar --}}
    <table class="meta-stats-table">
        <tr>
            <td class="meta-stat-cell">
                <span class="stat-value">{{ count($scopeSheet->zones) }}</span>
                <span class="stat-label">Zones</span>
            </td>
            <td class="meta-stat-cell">
                <span class="stat-value">{{ count($scopeSheet->presentations) }}</span>
                <span class="stat-label">Presentations</span>
            </td>
            <td class="meta-stat-cell">
                @php $totalPhotos = collect($zoneImages)->sum(fn($z) => count($z['images'])); @endphp
                <span class="stat-value">{{ $totalPhotos }}</span>
                <span class="stat-label">Zone Photos</span>
            </td>
            <td class="meta-stat-cell">
                <span class="stat-value">{{ \Carbon\Carbon::parse($date)->format('Y') }}</span>
                <span class="stat-label">Year</span>
            </td>
        </tr>
    </table>

    <main>

        {{-- ── INFO SECTION ── --}}
        <div class="section-heading">
            <div class="section-heading-bar">
                <span class="sh-dot"></span>
                <span class="sh-text">Claim Information</span>
                <span class="sh-accent-line"></span>
            </div>
            <div class="section-sub-bar"></div>
        </div>

        <table class="info-table">
            <tbody>
                <tr>
                    <td class="label">Claim Number</td>
                    <td class="value">{{ $scopeSheet->claimNumber ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Claim Internal ID</td>
                    <td class="value">{{ $scopeSheet->claimInternalId ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Property Address</td>
                    <td class="value">{{ $scopeSheet->propertyAddress ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Description</td>
                    <td class="value">{{ $scopeSheet->scopeSheetDescription ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Generated By</td>
                    <td class="value">{{ $scopeSheet->generatedByName ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Date Generated</td>
                    <td class="value">{{ $date }}</td>
                </tr>
            </tbody>
        </table>

        {{-- ── COVER PHOTO ── --}}
        @php
            $presentationImages = collect($presentationImages);
            $coverImage = $presentationImages->firstWhere('type', 'front_house')
                ?? $presentationImages->first();
        @endphp

        @if ($coverImage)
            <div class="section-heading">
                <div class="section-heading-bar">
                    <span class="sh-dot"></span>
                    <span class="sh-text">Property Overview</span>
                    <span class="sh-accent-line"></span>
                </div>
                <div class="section-sub-bar"></div>
            </div>

            <div class="cover-image-wrap">
                <img src="{{ $coverImage['path'] }}" alt="Cover Image" class="cover-image">
                <div class="cover-caption-bar">
                    <span class="cap-label">{{ $coverImage['type'] ?? 'Property Photo' }}</span>
                    <span class="cap-ref">{{ $scopeSheet->claimNumber ?? $scopeSheet->claimInternalId }}</span>
                </div>
            </div>
        @endif

        {{-- ════════════════════════════════════════════
             PRESENTATIONS SECTION
        ════════════════════════════════════════════ --}}
        @if ($presentationImages->isNotEmpty())
            <div class="page-break"></div>

            @php
                $totalPresentation = $presentationImages->count();
                $imagesPerPage     = 4;
                $presPages         = (int) ceil($totalPresentation / $imagesPerPage);
            @endphp

            @for ($page = 0; $page < $presPages; $page++)
                @if ($page > 0)
                    <div class="page-break"></div>
                @endif

                {{-- Header per page --}}
                <div class="header-logo-wrap">
                    <div class="header-accent-line"></div>
                    <div class="header-accent-line-2"></div>
                    <div class="header-logo-inner">
                        <div class="header-logo-left">
                            <img src="{{ $logoBase64 }}" alt="AquaShield Logo">
                            <div class="header-brand-block">
                                <div class="brand-name">AquaShield</div>
                                <div class="brand-tagline">Restoration &amp; Claims Management</div>
                            </div>
                        </div>
                        <div class="header-badge">
                            <div class="badge-label">Section</div>
                            <div class="badge-value">Presentations</div>
                            <div class="badge-label" style="margin-top:3px;">Page {{ $page + 1 }} / {{ $presPages }}</div>
                        </div>
                    </div>
                </div>

                <div class="section-heading" style="margin-top:14px;">
                    <div class="section-heading-bar">
                        <span class="sh-dot"></span>
                        <span class="sh-text">Presentation Photos</span>
                        <span class="sh-accent-line"></span>
                    </div>
                    <div class="section-sub-bar"></div>
                </div>

                <table class="photo-grid-table">
                    @for ($row = 0; $row < 2; $row++)
                        <tr>
                            @for ($col = 0; $col < 2; $col++)
                                @php $index = $page * $imagesPerPage + ($row * 2 + $col); @endphp
                                @if ($index < $totalPresentation)
                                    <td class="photo-card">
                                        <img src="{{ $presentationImages[$index]['path'] }}"
                                             alt="{{ $presentationImages[$index]['type'] ?? 'Presentation' }}">
                                        <div class="photo-card-caption">
                                            {{ $presentationImages[$index]['type'] ?? 'Presentation Photo' }}
                                            &nbsp;·&nbsp; #{{ $index + 1 }}
                                        </div>
                                    </td>
                                @else
                                    <td style="width:50%;"></td>
                                @endif
                            @endfor
                        </tr>
                    @endfor
                </table>
            @endfor
        @endif

        {{-- ════════════════════════════════════════════
             ZONES SECTION
        ════════════════════════════════════════════ --}}
        @foreach ($zoneImages as $zoneIdx => $zone)
            <div class="page-break"></div>

            {{-- Header per zone --}}
            <div class="header-logo-wrap">
                <div class="header-accent-line"></div>
                <div class="header-accent-line-2"></div>
                <div class="header-logo-inner">
                    <div class="header-logo-left">
                        <img src="{{ $logoBase64 }}" alt="AquaShield Logo">
                        <div class="header-brand-block">
                            <div class="brand-name">AquaShield</div>
                            <div class="brand-tagline">Restoration &amp; Claims Management</div>
                        </div>
                    </div>
                    <div class="header-badge">
                        <div class="badge-label">Zone {{ $zoneIdx + 1 }} / {{ count($zoneImages) }}</div>
                        <div class="badge-value">{{ Str::limit($zone['title'], 22) }}</div>
                    </div>
                </div>
            </div>

            {{-- Zone banner --}}
            <div class="zone-banner">
                <div class="zone-banner-title">{{ strtoupper($zone['title']) }}</div>
                <div class="zone-banner-sub">Damage Zone {{ $zoneIdx + 1 }} &nbsp;·&nbsp; Photographic Documentation</div>
            </div>

            @php
                $totalZoneImages   = count($zone['images']);
                $zoneImagesPerPage = 4;
                $zonePages         = max(1, (int) ceil($totalZoneImages / $zoneImagesPerPage));
            @endphp

            @for ($page = 0; $page < $zonePages; $page++)
                @if ($page > 0)
                    <div class="page-break"></div>

                    {{-- Repeat header for multi-page zones --}}
                    <div class="header-logo-wrap">
                        <div class="header-accent-line"></div>
                        <div class="header-accent-line-2"></div>
                        <div class="header-logo-inner">
                            <div class="header-logo-left">
                                <img src="{{ $logoBase64 }}" alt="AquaShield Logo">
                                <div class="header-brand-block">
                                    <div class="brand-name">AquaShield</div>
                                    <div class="brand-tagline">Restoration &amp; Claims Management</div>
                                </div>
                            </div>
                            <div class="header-badge">
                                <div class="badge-label">{{ $zone['title'] }}</div>
                                <div class="badge-value">Page {{ $page + 1 }} / {{ $zonePages }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="zone-banner">
                        <div class="zone-banner-title">{{ strtoupper($zone['title']) }}</div>
                        <div class="zone-banner-sub">Continued — Page {{ $page + 1 }} of {{ $zonePages }}</div>
                    </div>
                @endif

                <table class="photo-grid-table">
                    @for ($row = 0; $row < 2; $row++)
                        <tr>
                            @for ($col = 0; $col < 2; $col++)
                                @php $index = $page * $zoneImagesPerPage + ($row * 2 + $col); @endphp
                                @if ($index < $totalZoneImages)
                                    <td class="photo-card">
                                        <img src="{{ $zone['images'][$index]['path'] }}"
                                             alt="{{ $zone['images'][$index]['type'] ?? 'Zone photo' }}">
                                        <div class="photo-card-caption">
                                            {{ $zone['images'][$index]['type'] ?? 'Zone Photo' }}
                                            &nbsp;·&nbsp; {{ $zone['title'] }}
                                        </div>
                                    </td>
                                @else
                                    <td style="width:50%;"></td>
                                @endif
                            @endfor
                        </tr>
                    @endfor
                </table>
            @endfor

            @if (!empty($zone['notes']))
                <div class="zone-notes-block">
                    <div class="notes-label">Inspector Notes — {{ $zone['title'] }}</div>
                    <div class="notes-text">{{ $zone['notes'] }}</div>
                </div>
            @endif
        @endforeach

        {{-- ════════════════════════════════════════════
             SIGNATURE PAGE
        ════════════════════════════════════════════ --}}
        <div class="page-break"></div>

        {{-- Header --}}
        <div class="header-logo-wrap">
            <div class="header-accent-line"></div>
            <div class="header-accent-line-2"></div>
            <div class="header-logo-inner">
                <div class="header-logo-left">
                    <img src="{{ $logoBase64 }}" alt="AquaShield Logo">
                    <div class="header-brand-block">
                        <div class="brand-name">AquaShield</div>
                        <div class="brand-tagline">Restoration &amp; Claims Management</div>
                    </div>
                </div>
                <div class="header-badge">
                    <div class="badge-label">Section</div>
                    <div class="badge-value">Authorization</div>
                </div>
            </div>
        </div>

        <div class="section-heading" style="margin-top:16px;">
            <div class="section-heading-bar">
                <span class="sh-dot"></span>
                <span class="sh-text">Authorization &amp; Signatures</span>
                <span class="sh-accent-line"></span>
            </div>
            <div class="section-sub-bar"></div>
        </div>

        <div class="signature-section-intro">
            <p>
                The undersigned parties confirm that the information contained in this Scope Sheet accurately
                reflects the damage assessment conducted at the property referenced above. Both parties authorize
                AquaShield CRM to proceed with the restoration services as documented herein.
            </p>
        </div>

        <table class="signature-table">
            <tbody>
                <tr>
                    <td class="signature-card">
                        <div class="signature-spacer"></div>
                        <hr class="signature-line">
                        <div class="signature-name">{{ $scopeSheet->generatedByName ?? 'Inspector' }}</div>
                        <div class="signature-role">AquaShield Inspector</div>
                        <div class="signature-date">
                            Date: &nbsp;<strong>{{ $date }}</strong>
                        </div>
                    </td>
                    <td class="signature-card">
                        <div class="signature-spacer"></div>
                        <hr class="signature-line">
                        <div class="signature-name">Property Owner</div>
                        <div class="signature-role">Client / Homeowner</div>
                        <div class="signature-date">
                            Date: &nbsp;<strong>{{ $date }}</strong>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Footer declaration strip --}}
        <table style="width:100%; margin-top:20px; border-collapse:collapse;">
            <tr>
                <td style="border-top:2px solid #00B5E2; padding-top:10px; font-size:8px; color:#718096; text-align:center; line-height:1.7;">
                    This document is <strong style="color:#0C2340;">CONFIDENTIAL</strong> and intended solely for the named parties.
                    &nbsp;·&nbsp; Generated by AquaShield CRM &nbsp;·&nbsp; {{ $generatedAt }}
                    &nbsp;·&nbsp; Claim Ref: <strong style="color:#00B5E2;">{{ $scopeSheet->claimNumber ?? $scopeSheet->claimInternalId }}</strong>
                </td>
            </tr>
        </table>

    </main>

    {{-- ── Fixed footer (every page) ── --}}
    <div class="footer">
        <div class="footer-accent"></div>
        <div class="footer-bar">
            <div class="footer-brand">
                <strong>AquaShield CRM</strong>
                &nbsp;·&nbsp; Scope Sheet &nbsp;·&nbsp;
                {{ $scopeSheet->claimNumber ?? $scopeSheet->claimInternalId }}
            </div>
            <div class="footer-center">Confidential · Property Damage Assessment</div>
            <div class="footer-page">Page <span class="page-number"></span></div>
        </div>
    </div>

</body>
</html>
