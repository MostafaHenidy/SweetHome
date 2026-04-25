# Phase 4: Services & Content Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the public-facing content pages — services page, contact form with email notifications, product reviews system, newsletter subscription, and a projects/portfolio page.

**Architecture:** Livewire components for interactive forms (contact, reviews, newsletter). Blade views for static content pages. All forms validated server-side with email notifications wired to the mailables from Phase 3.

**Tech Stack:** Livewire 3, Alpine.js, Tailwind CSS, Laravel Mailables

**Prerequisites:** Phase 1 (models, migrations, seeders) and Phase 3 (email mailables) complete.

---

### Task 1: Create Services Page

**Files:**
- Create: `resources/views/services.blade.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Create services page**

Create `resources/views/services.blade.php`:
```blade
@extends('layouts.app-shop')

@section('content')
<!-- Hero -->
<div class="relative h-[50vh] min-h-[400px] overflow-hidden">
    <img src="https://images.unsplash.com/photo-1581858726788-75bc0f6a9f4d?w=1600"
         alt="Servizi SweetHome"
         class="w-full h-full object-cover">
    <div class="absolute inset-0 bg-brand-dark/70"></div>
    <div class="absolute inset-0 flex items-center justify-center">
        <div class="text-center px-4">
            <h1 class="text-4xl md:text-6xl font-serif font-bold text-brand-light mb-4">I Nostri Servizi</h1>
            <p class="text-lg text-gray-300 max-w-2xl mx-auto">
                Dalla consulenza alla consegna, ti accompagniamo in ogni fase del tuo progetto.
            </p>
        </div>
    </div>
</div>

<!-- Services Detail -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
        <!-- Consulenza -->
        <div class="group">
            <div class="w-16 h-16 rounded-full bg-brand-gold/10 flex items-center justify-center mb-6 group-hover:bg-brand-gold/20 transition">
                <svg class="w-8 h-8 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <h3 class="text-xl font-serif text-brand-light mb-3">Consulenza Personalizzata</h3>
            <p class="text-gray-400 leading-relaxed mb-4">
                I nostri esperti ti guidano nella scelta dei materiali più adatti al tuo progetto,
                considerando stile, budget e funzionalità.
            </p>
            <ul class="space-y-2 text-sm text-gray-500">
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-gold" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Sopralluogo gratuito
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-gold" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Preventivo dettagliato
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-gold" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Campioni materiali
                </li>
            </ul>
        </div>

        <!-- Progettazione -->
        <div class="group">
            <div class="w-16 h-16 rounded-full bg-brand-gold/10 flex items-center justify-center mb-6 group-hover:bg-brand-gold/20 transition">
                <svg class="w-8 h-8 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="text-xl font-serif text-brand-light mb-3">Progettazione 3D</h3>
            <p class="text-gray-400 leading-relaxed mb-4">
                Visualizza il risultato finale con il nostro servizio di rendering 3D.
                Vedrai come i materiali scelti si integreranno nel tuo spazio.
            </p>
            <ul class="space-y-2 text-sm text-gray-500">
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-gold" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Rendering fotorealistici
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-gold" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Planimetrie tecniche
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-gold" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Revisioni illimitate
                </li>
            </ul>
        </div>

        <!-- Consegna e Posa -->
        <div class="group">
            <div class="w-16 h-16 rounded-full bg-brand-gold/10 flex items-center justify-center mb-6 group-hover:bg-brand-gold/20 transition">
                <svg class="w-8 h-8 text-brand-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                </svg>
            </div>
            <h3 class="text-xl font-serif text-brand-light mb-3">Consegna e Posa</h3>
            <p class="text-gray-400 leading-relaxed mb-4">
                Consegna a domicilio in tutta Italia e servizio di posa in opera
                con professionisti qualificati.
            </p>
            <ul class="space-y-2 text-sm text-gray-500">
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-gold" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Consegna assicurata
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-gold" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Posa certificata
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-gold" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Garanzia sul lavoro
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- CTA -->
<div class="bg-brand-slate/20 border-y border-white/5">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
        <h2 class="text-3xl font-serif font-bold text-brand-light mb-4">Hai un progetto in mente?</h2>
        <p class="text-gray-400 mb-8 max-w-xl mx-auto">
            Contattaci per una consulenza gratuita. Ti aiuteremo a trasformare la tua visione in realtà.
        </p>
        <a href="{{ route('contact') }}"
           class="inline-block px-8 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">
            Richiedi Consulenza
        </a>
    </div>
</div>
@endsection
```

- [ ] **Step 2: Add services route**

Modify `routes/web.php`:
```php
Route::view('/services', 'services')->name('services');
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/services.blade.php routes/web.php
git commit -m "feat: create services page with 3 service cards and CTA"
```

---

### Task 2: Create Contact Page with Livewire Form

**Files:**
- Create: `app/Http/Livewire/ContactForm.php`
- Create: `resources/views/livewire/contact-form.blade.php`
- Create: `resources/views/contact.blade.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Create ContactForm Livewire component**

Create `app/Http/Livewire/ContactForm.php`:
```php
<?php

namespace App\Http\Livewire;

use App\Mail\ContactFormNotification;
use App\Models\ContactSubmission;
use Livewire\Component;
use Illuminate\Support\Facades\Mail;

class ContactForm extends Component
{
    public $name = '';
    public $email = '';
    public $phone = '';
    public $subject = '';
    public $message = '';
    public $success = false;

    protected $rules = [
        'name' => 'required|string|min:2|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:20',
        'subject' => 'required|string|min:3|max:255',
        'message' => 'required|string|min:10|max:5000',
    ];

    protected $messages = [
        'name.required' => 'Il nome è obbligatorio.',
        'email.required' => "L'email è obbligatoria.",
        'email.email' => "Inserisci un'email valida.",
        'subject.required' => "L'oggetto è obbligatorio.",
        'message.required' => 'Il messaggio è obbligatorio.',
        'message.min' => 'Il messaggio deve contenere almeno 10 caratteri.',
    ];

    public function submit()
    {
        $this->validate();

        $submission = ContactSubmission::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'subject' => $this->subject,
            'message' => $this->message,
        ]);

        // Send notification to admin
        $adminEmail = \App\Models\Setting::get('site_email', 'info@sweethome.it');
        Mail::to($adminEmail)->send(new ContactFormNotification($submission));

        $this->success = true;
        $this->reset(['name', 'email', 'phone', 'subject', 'message']);
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
```

- [ ] **Step 2: Create contact form view**

Create `resources/views/livewire/contact-form.blade.php`:
```blade
<div>
    @if($success)
        <div class="text-center py-12">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-500/20 flex items-center justify-center">
                <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="text-xl font-serif text-brand-light mb-2">Messaggio Inviato!</h3>
            <p class="text-gray-400 mb-6">Grazie per averci contattato. Ti risponderemo al più presto.</p>
            <button wire:click="$set('success', false)"
                    class="text-brand-gold hover:text-brand-light transition underline text-sm">
                Invia un altro messaggio
            </button>
        </div>
    @else
        <form wire:submit="submit" class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Nome *</label>
                    <input type="text" wire:model="name"
                           class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('name') border-red-500 @enderror">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Email *</label>
                    <input type="email" wire:model="email"
                           class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('email') border-red-500 @enderror">
                    @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-1">Telefono</label>
                <input type="tel" wire:model="phone"
                       class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition">
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-1">Oggetto *</label>
                <input type="text" wire:model="subject"
                       class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('subject') border-red-500 @enderror">
                @error('subject')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-1">Messaggio *</label>
                <textarea wire:model="message" rows="6"
                          class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('message') border-red-500 @enderror"></textarea>
                @error('message')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                    class="w-full px-8 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">
                Invia Messaggio
            </button>
        </form>
    @endif
</div>
```

- [ ] **Step 3: Create contact page**

Create `resources/views/contact.blade.php`:
```blade
@extends('layouts.app-shop')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl md:text-4xl font-serif font-bold text-brand-light mb-8">Contattaci</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Form -->
        <div>
            @livewire('contact-form')
        </div>

        <!-- Contact Info -->
        <div class="space-y-8">
            <div>
                <h3 class="text-lg font-serif text-brand-gold mb-4">Showroom</h3>
                <p class="text-gray-400 leading-relaxed">
                    {{ \App\Models\Setting::get('site_address', 'Via Carlo Espinasse 104/a, 20156 Milano MI') }}
                </p>
            </div>

            <div>
                <h3 class="text-lg font-serif text-brand-gold mb-4">Orari</h3>
                <div class="space-y-2 text-sm text-gray-400">
                    <div class="flex justify-between">
                        <span>Lunedì - Venerdì</span>
                        <span>9:00 - 18:30</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Sabato</span>
                        <span>9:00 - 13:00</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Domenica</span>
                        <span>Chiuso</span>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-serif text-brand-gold mb-4">Contatti</h3>
                <div class="space-y-2 text-sm text-gray-400">
                    <p>
                        <a href="tel:{{ \App\Models\Setting::get('site_phone', '3288995628') }}" class="hover:text-brand-light transition">
                            {{ \App\Models\Setting::get('site_phone', '328 899 5628') }}
                        </a>
                    </p>
                    <p>
                        <a href="mailto:{{ \App\Models\Setting::get('site_email', 'info@sweethome.it') }}" class="hover:text-brand-light transition">
                            {{ \App\Models\Setting::get('site_email', 'info@sweethome.it') }}
                        </a>
                    </p>
                </div>
            </div>

            <!-- Map placeholder -->
            <div class="rounded-sm overflow-hidden bg-brand-slate/20 h-64 flex items-center justify-center">
                <p class="text-gray-500 text-sm">Mappa Google Maps</p>
            </div>
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 4: Add contact route**

Modify `routes/web.php`:
```php
Route::view('/contact', 'contact')->name('contact');
```

- [ ] **Step 5: Commit**

```bash
git add app/Http/Livewire/ContactForm.php resources/views/livewire/contact-form.blade.php resources/views/contact.blade.php routes/web.php
git commit -m "feat: create contact page with Livewire form and email notification"
```

---

### Task 3: Create Product Review System

**Files:**
- Create: `app/Http/Livewire/ProductReviews.php`
- Create: `resources/views/livewire/product-reviews.blade.php`
- Modify: `resources/views/livewire/product-detail.blade.php`

- [ ] **Step 1: Create ProductReviews Livewire component**

Create `app/Http/Livewire/ProductReviews.php`:
```php
<?php

namespace App\Http\Livewire;

use App\Models\Product;
use App\Models\Review;
use Livewire\Component;

class ProductReviews extends Component
{
    public $product;
    public $user_name = '';
    public $user_email = '';
    public $rating = 5;
    public $comment = '';
    public $success = false;

    protected $rules = [
        'user_name' => 'required|string|min:2|max:255',
        'user_email' => 'required|email|max:255',
        'rating' => 'required|integer|min:1|max:5',
        'comment' => 'required|string|min:10|max:2000',
    ];

    public function mount($product)
    {
        $this->product = $product;
    }

    public function submitReview()
    {
        $this->validate();

        Review::create([
            'product_id' => $this->product->id,
            'user_name' => $this->user_name,
            'user_email' => $this->user_email,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'status' => 'pending',
        ]);

        $this->success = true;
        $this->reset(['user_name', 'user_email', 'comment']);
        $this->rating = 5;
    }

    public function render()
    {
        $reviews = Review::where('product_id', $this->product->id)
            ->approved()
            ->latest()
            ->get();

        $averageRating = $reviews->avg('rating') ?? 0;
        $totalReviews = $reviews->count();

        return view('livewire.product-reviews', [
            'reviews' => $reviews,
            'averageRating' => round($averageRating, 1),
            'totalReviews' => $totalReviews,
        ]);
    }
}
```

- [ ] **Step 2: Create product reviews view**

Create `resources/views/livewire/product-reviews.blade.php`:
```blade
<div class="mt-16">
    <h2 class="text-2xl font-serif font-bold text-brand-light mb-8">Recensioni</h2>

    <!-- Summary -->
    <div class="bg-brand-slate/10 border border-white/5 rounded-sm p-6 mb-8">
        <div class="flex items-center gap-6">
            <div class="text-center">
                <div class="text-4xl font-bold text-brand-gold">{{ $averageRating }}</div>
                <div class="flex text-brand-gold mt-2">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-5 h-5 {{ $i <= round($averageRating) ? 'fill-current' : 'text-gray-600' }}" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                </div>
                <p class="text-sm text-gray-400 mt-1">{{ $totalReviews }} recensioni</p>
            </div>
        </div>
    </div>

    <!-- Reviews List -->
    @if($reviews->count() > 0)
        <div class="space-y-6 mb-12">
            @foreach($reviews as $review)
                <div class="border-b border-white/5 pb-6">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="flex text-brand-gold">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $review->rating ? 'fill-current' : 'text-gray-600' }}" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <span class="text-sm text-gray-400">{{ $review->user_name }}</span>
                        <span class="text-xs text-gray-600">{{ $review->created_at->format('d/m/Y') }}</span>
                    </div>
                    @if($review->comment)
                        <p class="text-sm text-gray-300 leading-relaxed">{{ $review->comment }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-400 mb-8">Nessuna recensione ancora. Sii il primo!</p>
    @endif

    <!-- Write Review Form -->
    <div class="bg-brand-slate/10 border border-white/5 rounded-sm p-6">
        <h3 class="text-lg font-serif text-brand-light mb-6">Scrivi una Recensione</h3>

        @if($success)
            <div class="text-center py-6">
                <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-green-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-brand-light mb-2">Grazie per la tua recensione!</p>
                <p class="text-sm text-gray-400">Sarà pubblicata dopo l'approvazione.</p>
            </div>
        @else
            <form wire:submit="submitReview" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Nome *</label>
                        <input type="text" wire:model="user_name"
                               class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('user_name') border-red-500 @enderror">
                        @error('user_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Email *</label>
                        <input type="email" wire:model="user_email"
                               class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('user_email') border-red-500 @enderror">
                        @error('user_email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-2">Valutazione</label>
                    <div class="flex gap-1">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" wire:click="$set('rating', {{ $i }})"
                                    class="w-8 h-8 {{ $i <= $rating ? 'text-brand-gold' : 'text-gray-600' }} hover:text-brand-gold transition">
                                <svg fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </button>
                        @endfor
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1">Recensione *</label>
                    <textarea wire:model="comment" rows="4"
                              class="w-full px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light rounded-sm focus:outline-none focus:border-brand-gold transition @error('comment') border-red-500 @enderror"></textarea>
                    @error('comment')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit"
                        class="px-8 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">
                    Invia Recensione
                </button>
            </form>
        @endif
    </div>
</div>
```

- [ ] **Step 3: Integrate reviews into product detail page**

Modify `resources/views/livewire/product-detail.blade.php` — replace the reviews tab content with the Livewire component.

In the reviews tab section, replace the existing content with:
```blade
<div x-show="tab === 'reviews'" x-cloak>
    @livewire('product-reviews', ['product' => $product])
</div>
```

- [ ] **Step 4: Commit**

```bash
git add app/Http/Livewire/ProductReviews.php resources/views/livewire/product-reviews.blade.php resources/views/livewire/product-detail.blade.php
git commit -m "feat: create product review system with star rating and approval workflow"
```

---

### Task 4: Create Newsletter Subscription

**Files:**
- Create: `app/Http/Livewire/NewsletterSignup.php`
- Create: `resources/views/livewire/newsletter-signup.blade.php`
- Modify: `resources/views/home.blade.php`

- [ ] **Step 1: Create NewsletterSignup Livewire component**

Create `app/Http/Livewire/NewsletterSignup.php`:
```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;

class NewsletterSignup extends Component
{
    public $email = '';
    public $success = false;

    protected $rules = [
        'email' => 'required|email|max:255',
    ];

    public function subscribe()
    {
        $this->validate();

        // Store in settings as JSON array (simple approach, replace with dedicated table if needed)
        $subscribers = \App\Models\Setting::get('newsletter_subscribers', []);
        if (!in_array($this->email, $subscribers)) {
            $subscribers[] = $this->email;
            \App\Models\Setting::set('newsletter_subscribers', $subscribers, 'newsletter');
        }

        $this->success = true;
        $this->reset('email');
    }

    public function render()
    {
        return view('livewire.newsletter-signup');
    }
}
```

- [ ] **Step 2: Create newsletter signup view**

Create `resources/views/livewire/newsletter-signup.blade.php`:
```blade
<div>
    @if($success)
        <p class="text-brand-gold text-sm">Iscritto con successo! Grazie.</p>
    @else
        <form wire:submit="subscribe" class="flex gap-3">
            <input type="email" wire:model="email"
                   placeholder="La tua email"
                   class="flex-1 px-4 py-3 bg-brand-dark/50 border border-white/10 text-brand-light placeholder-gray-500 rounded-sm focus:outline-none focus:border-brand-gold transition @error('email') border-red-500 @enderror">
            <button type="submit"
                    class="px-6 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm whitespace-nowrap">
                Iscriviti
            </button>
        </form>
        @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
    @endif
</div>
```

- [ ] **Step 3: Update homepage newsletter section**

Modify `resources/views/home.blade.php` — find the newsletter section and replace the static form with:
```blade
@livewire('newsletter-signup')
```

- [ ] **Step 4: Commit**

```bash
git add app/Http/Livewire/NewsletterSignup.php resources/views/livewire/newsletter-signup.blade.php resources/views/home.blade.php
git commit -m "feat: create newsletter signup Livewire component"
```

---

### Task 5: Create Projects/Portfolio Page

**Files:**
- Create: `resources/views/projects.blade.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Create projects page**

Create `resources/views/projects.blade.php`:
```blade
@extends('layouts.app-shop')

@section('content')
<!-- Hero -->
<div class="relative h-[50vh] min-h-[400px] overflow-hidden">
    <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1600"
         alt="Progetti SweetHome"
         class="w-full h-full object-cover">
    <div class="absolute inset-0 bg-brand-dark/70"></div>
    <div class="absolute inset-0 flex items-center justify-center">
        <div class="text-center px-4">
            <h1 class="text-4xl md:text-6xl font-serif font-bold text-brand-light mb-4">I Nostri Progetti</h1>
            <p class="text-lg text-gray-300 max-w-2xl mx-auto">
                Scopri come abbiamo trasformato gli spazi dei nostri clienti.
            </p>
        </div>
    </div>
</div>

<!-- Projects Grid -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Project 1 -->
        <div class="group relative overflow-hidden rounded-sm">
            <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800"
                 alt="Bagno moderno"
                 loading="lazy"
                 class="w-full h-[400px] object-cover group-hover:scale-105 transition-transform duration-700">
            <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/90 via-brand-dark/30 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 p-6">
                <h3 class="text-xl font-serif text-brand-light mb-1">Bagno Moderno</h3>
                <p class="text-sm text-gray-300">Milano, 2024</p>
            </div>
        </div>

        <!-- Project 2 -->
        <div class="group relative overflow-hidden rounded-sm">
            <img src="https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800"
                 alt="Living contemporaneo"
                 loading="lazy"
                 class="w-full h-[400px] object-cover group-hover:scale-105 transition-transform duration-700">
            <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/90 via-brand-dark/30 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 p-6">
                <h3 class="text-xl font-serif text-brand-light mb-1">Living Contemporaneo</h3>
                <p class="text-sm text-gray-300">Monza, 2024</p>
            </div>
        </div>

        <!-- Project 3 -->
        <div class="group relative overflow-hidden rounded-sm">
            <img src="https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=800"
                 alt="Cucina di lusso"
                 loading="lazy"
                 class="w-full h-[400px] object-cover group-hover:scale-105 transition-transform duration-700">
            <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/90 via-brand-dark/30 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 p-6">
                <h3 class="text-xl font-serif text-brand-light mb-1">Cucina di Lusso</h3>
                <p class="text-sm text-gray-300">Como, 2023</p>
            </div>
        </div>

        <!-- Project 4 -->
        <div class="group relative overflow-hidden rounded-sm">
            <img src="https://images.unsplash.com/photo-1600573472592-401b489a3cdc?w=800"
                 alt="Terrazza urbana"
                 loading="lazy"
                 class="w-full h-[400px] object-cover group-hover:scale-105 transition-transform duration-700">
            <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/90 via-brand-dark/30 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 p-6">
                <h3 class="text-xl font-serif text-brand-light mb-1">Terrazza Urbana</h3>
                <p class="text-sm text-gray-300">Milano, 2023</p>
            </div>
        </div>
    </div>
</div>

<!-- CTA -->
<div class="bg-brand-slate/20 border-y border-white/5">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
        <h2 class="text-3xl font-serif font-bold text-brand-light mb-4">Vuoi realizzare il tuo progetto?</h2>
        <p class="text-gray-400 mb-8 max-w-xl mx-auto">
            Contattaci per una consulenza gratuita. Il nostro team è pronto ad aiutarti.
        </p>
        <a href="{{ route('contact') }}"
           class="inline-block px-8 py-3 bg-brand-gold text-brand-dark text-sm font-semibold tracking-widest uppercase hover:bg-opacity-90 transition rounded-sm">
            Contattaci
        </a>
    </div>
</div>
@endsection
```

- [ ] **Step 2: Add projects route**

Modify `routes/web.php`:
```php
Route::view('/projects', 'projects')->name('projects');
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/projects.blade.php routes/web.php
git commit -m "feat: create projects/portfolio page with 4 project showcases"
```

---

### Task 6: Add Filament Management for Reviews & Contact Submissions

**Files:**
- Modify: `app/Filament/Resources/ReviewResource.php`
- Modify: `app/Filament/Resources/ContactSubmissionResource.php`

- [ ] **Step 1: Add bulk approve/reject to ReviewResource**

Modify `app/Filament/Resources/ReviewResource.php` — add bulk actions:

```php
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

// In the table configuration:
->bulkActions([
    BulkAction::make('approve')
        ->label('Approva')
        ->action(fn(Collection $records) => $records->each->update(['status' => 'approved'])),
    BulkAction::make('reject')
        ->label('Rifiuta')
        ->action(fn(Collection $records) => $records->each->update(['status' => 'rejected'])),
    BulkAction::make('delete')
        ->label('Elimina')
        ->requiresConfirmation()
        ->action(fn(Collection $records) => $records->each->delete()),
])
```

- [ ] **Step 2: Add mark as replied to ContactSubmissionResource**

Modify `app/Filament/Resources/ContactSubmissionResource.php` — add actions:

```php
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;

// In the table configuration:
->actions([
    Action::make('markRead')
        ->label('Segna come letto')
        ->icon('heroicon-o-eye')
        ->action(fn(ContactSubmission $record) => $record->update(['status' => 'read'])),
    Action::make('markReplied')
        ->label('Segna come risposto')
        ->icon('heroicon-o-envelope')
        ->action(fn(ContactSubmission $record) => $record->update(['status' => 'replied'])),
])
->bulkActions([
    BulkAction::make('markRead')
        ->label('Segna come letti')
        ->action(fn(Collection $records) => $records->each->update(['status' => 'read'])),
    BulkAction::make('markReplied')
        ->label('Segna come risposte')
        ->action(fn(Collection $records) => $records->each->update(['status' => 'replied'])),
])
```

- [ ] **Step 3: Add newsletter subscribers view to Settings**

Modify `app/Filament/Pages/Settings.php` — add a newsletter section to the form:

```php
Section::make('Newsletter')
    ->schema([
        Textarea::make('newsletter_subscribers')
            ->label('Iscritti (uno per riga)')
            ->rows(8)
            ->helperText('Gli iscritti alla newsletter.'),
    ]),
```

And in the `mount()` method, add:
```php
'newsletter_subscribers' => implode("\n", Setting::get('newsletter_subscribers', [])),
```

In the `save()` method, handle the newline-separated conversion:
```php
if ($key === 'newsletter_subscribers') {
    $value = array_filter(array_map('trim', explode("\n", $value ?? '')));
}
```

- [ ] **Step 4: Commit**

```bash
git add app/Filament/Resources/ReviewResource.php app/Filament/Resources/ContactSubmissionResource.php app/Filament/Pages/Settings.php
git commit -m "feat: add bulk actions for reviews and contact submissions in Filament admin"
```

---

## Phase 4 Summary

After completing this phase you will have:
- Services page with 3 detailed service cards and CTA
- Contact page with validated Livewire form, database storage, and email notification to admin
- Product review system with star rating, submission form, and approval workflow (reviews are pending until admin approves)
- Newsletter signup component on homepage with subscriber storage
- Projects/portfolio page with 4 showcase projects
- Filament admin bulk actions for approving/rejecting reviews and managing contact submissions
- Newsletter subscriber management in Settings page
