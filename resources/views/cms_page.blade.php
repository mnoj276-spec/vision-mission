@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
<div style="max-width: 900px; margin: 3rem auto; padding: 0 1.5rem;">
    <!-- Breadcrumbs -->
    <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.5rem; display: flex; gap: 0.5rem; align-items: center;">
        <a href="/" style="color: var(--text-secondary); text-decoration: none;">Home</a>
        <span>&rsaquo;</span>
        <span style="color: var(--text-primary); font-weight: 500;">{{ $page->title }}</span>
    </div>

    <!-- Glassmorphic Content Card -->
    <article class="glass-panel" style="padding: 2.5rem 2rem; border-radius: 20px; box-shadow: var(--card-shadow);">
        <header style="margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem;">
            <h1 style="font-family: 'Outfit'; font-size: 2.5rem; line-height: 1.2; margin: 0 0 0.5rem 0; color: var(--text-primary);">
                {{ $page->title }}
            </h1>
            <div style="font-size: 0.8rem; color: var(--text-secondary);">
                Last Updated: {{ $page->updated_at->format('d M Y') }}
            </div>
        </header>

        <!-- Dynamic CMS Content Area -->
        <div class="cms-page-content" style="color: var(--text-primary); line-height: 1.8; font-size: 1.05rem;">
            {!! $page->content !!}
        </div>
    </article>
</div>
@endsection
