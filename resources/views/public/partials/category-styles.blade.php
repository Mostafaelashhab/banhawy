<style>
.cat-hero {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #001B2A 0%, #0E2E3F 100%);
    color: white;
    border-radius: 18px;
    padding: 18px;
    display: flex;
    align-items: center;
    gap: 14px;
}
.cat-hero-text { flex: 1; min-width: 0; position: relative; z-index: 1; }
.cat-hero-eyebrow {
    display: inline-block;
    padding: 3px 10px;
    background: rgba(255,255,255,.14);
    border-radius: 7px;
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: .3px;
    margin-bottom: 8px;
}
.cat-hero-title {
    font-size: 19px;
    font-weight: 900;
    letter-spacing: -.3px;
    margin: 0 0 4px;
    line-height: 1.3;
}
.cat-hero-sub {
    font-size: 12px;
    color: rgba(255,255,255,.75);
    font-weight: 600;
    margin: 0;
    line-height: 1.6;
}
.cat-hero-ico {
    width: 64px; height: 64px;
    border-radius: 16px;
    background: rgba(255,255,255,.10);
    display: grid; place-items: center;
    flex-shrink: 0;
}

.cat-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    margin-bottom: 8px;
    text-decoration: none;
    color: var(--ink-1);
    transition: transform .12s ease, box-shadow .15s ease;
}
.cat-card:active { transform: scale(.99); }
.cat-card-thumb {
    width: 56px; height: 56px;
    border-radius: 13px;
    overflow: hidden;
    flex-shrink: 0;
    display: grid;
    place-items: center;
    color: white;
    font-weight: 900;
    font-size: 14px;
}
.cat-card-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.cat-card-name {
    font-weight: 900;
    font-size: 14px;
    line-height: 1.35;
}
.cat-card-cat {
    font-size: 11.5px;
    color: var(--ink-3);
    font-weight: 700;
    margin-top: 2px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.cat-card-meta {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 6px;
    font-size: 11px;
    color: var(--ink-3);
    font-weight: 700;
}
.cat-card-meta .dot { width: 3px; height: 3px; background: var(--ink-4); border-radius: 50%; }
.cat-arrow {
    flex-shrink: 0;
    color: var(--ink-4);
}

.cat-verified {
    display: inline-block;
    width: 16px; height: 16px;
    background: var(--teal);
    color: white;
    border-radius: 50%;
    text-align: center;
    line-height: 16px;
    font-size: 10px;
    font-weight: 900;
    flex-shrink: 0;
}
.cat-badge {
    margin-right: auto;
    padding: 2px 7px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .2px;
}
.cat-badge-business {
    background: linear-gradient(135deg, #FBBF24, #F59E0B);
    color: white;
}
.cat-badge-pro {
    background: rgba(13,148,136,.14);
    color: var(--teal);
}
</style>
