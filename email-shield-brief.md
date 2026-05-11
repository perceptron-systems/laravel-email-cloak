# 📦 Package Laravel — Obfuscation email **100% CSS, 0 JS**
> Prompt de conception – base pour générer le package

## 🎯 Objectif
Créer un package Laravel qui fournit une **protection anti-spam efficace pour les adresses e-mails**,  
via :
- une **directive Blade** `@obfuscatedEmail()`
- une **route mailto sécurisée**
- **aucun JavaScript** requis
- **obfuscation CSS pure**
- **compatible Core Web Vitals & PageSpeed**

## 🚨 Le problème
Afficher une adresse e-mail dans le HTML :
- scrapé par les robots
- montée du spam
- difficile à sécuriser sans JS

Les solutions existantes : JS / regex / middleware → ralentissent le rendu, cassent les pages, baissent la note PageSpeed.

## 💡 Proposition
Un package Laravel **zéro JS**, orienté performances :
- découpe l’adresse côté Blade (`user` + `domain`)
- affiche via `data-*`
- rendu visuel **uniquement via CSS**
- mail reconstruit serveur-side (route `/contact-email`)
- email jamais visible dans le DOM

## 🧪 Exemple Blade
```blade
@obfuscatedEmail('contact@monsite.fr')
```

### HTML généré
```html
<a href="/contact-email"
   data-user="contact"
   data-domain="monsite.fr"
   class="obf-email">[email protected]</a>
```

### CSS
```css
.obf-email::before {
  content: attr(data-user) '@' attr(data-domain);
}
```

## 🔐 Avantages
- ⚡ 0 JavaScript (Core Web Vitals friendly)
- 🛡️ email invisible aux bots DOM
- 🔍 compatible AMP / Googlebot No-JS
- 🎯 adoption simple : `@obfuscatedEmail()`

## 📦 Contenu du package
- directive Blade
- route mailto sécurisée
- fichier CSS publishable
- config `.env` / `config/email-shield.php`
- tests garantissant non-exposition email

## 🎯 Positionnement marketing
> “Obfuscation email Laravel — 100% CSS, 0 JS, 0 impact PageSpeed.”
Différencieurs :
- aucun middleware
- aucune analyse d’output
- aucun JS

Public cible :
- sites corporate
- SEO / PageSpeed addicts
- institutions low-JS

## 🔄 Roadmap
v1 — directive + route + css + tests  
v1.1 — labels custom / SR-only  
v2 — `<x-email-shield />`  
v3 — livewire stateless (package sœur ou PR Flux UI)

## 🧨 Concurrence
Muddle — obfuscation JS possible  
gremo/email-obfuscator — middleware lourd  
→ Aucun package **0 JS + route proxy mailto**

## 🎬 Conclusion
Une solution :
- performante
- discrète
- fiable
- unique dans l’écosystème Laravel

Prépare le terrain pour un second projet Livewire / Flux UI.
