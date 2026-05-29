---
name: Clinical Precision
colors:
  surface: '#f8f9ff'
  surface-dim: '#cbdbf5'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff4ff'
  surface-container: '#e5eeff'
  surface-container-high: '#dce9ff'
  surface-container-highest: '#d3e4fe'
  on-surface: '#0b1c30'
  on-surface-variant: '#444653'
  inverse-surface: '#213145'
  inverse-on-surface: '#eaf1ff'
  outline: '#757684'
  outline-variant: '#c4c5d5'
  surface-tint: '#3755c3'
  primary: '#00288e'
  on-primary: '#ffffff'
  primary-container: '#1e40af'
  on-primary-container: '#a8b8ff'
  inverse-primary: '#b8c4ff'
  secondary: '#006c49'
  on-secondary: '#ffffff'
  secondary-container: '#6cf8bb'
  on-secondary-container: '#00714d'
  tertiary: '#4c2e00'
  on-tertiary: '#ffffff'
  tertiary-container: '#6b4200'
  on-tertiary-container: '#ffa929'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dde1ff'
  primary-fixed-dim: '#b8c4ff'
  on-primary-fixed: '#001453'
  on-primary-fixed-variant: '#173bab'
  secondary-fixed: '#6ffbbe'
  secondary-fixed-dim: '#4edea3'
  on-secondary-fixed: '#002113'
  on-secondary-fixed-variant: '#005236'
  tertiary-fixed: '#ffddb8'
  tertiary-fixed-dim: '#ffb95f'
  on-tertiary-fixed: '#2a1700'
  on-tertiary-fixed-variant: '#653e00'
  background: '#f8f9ff'
  on-background: '#0b1c30'
  surface-variant: '#d3e4fe'
typography:
  display-lg:
    fontFamily: Inter
    fontSize: 36px
    fontWeight: '700'
    lineHeight: 44px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Inter
    fontSize: 28px
    fontWeight: '600'
    lineHeight: 36px
    letterSpacing: -0.01em
  headline-lg-mobile:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  title-md:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-caps:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '700'
    lineHeight: 16px
    letterSpacing: 0.05em
  code-sm:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '500'
    lineHeight: 18px
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  base: 8px
  xs: 4px
  sm: 12px
  md: 16px
  lg: 24px
  xl: 32px
  2xl: 48px
  gutter: 24px
  margin: 32px
---

## Brand & Style

The design system is anchored in the principles of reliability, clinical efficiency, and cognitive clarity. Designed specifically for healthcare administration, the aesthetic prioritizes information density without sacrificing readability. 

The style is **Corporate / Modern** with a strong **Minimalist** influence. It utilizes generous white space to reduce "dashboard fatigue," ensuring that critical medical data and patient information remain the focal point. The emotional response should be one of calm authority—reassuring the user that the data is organized, secure, and actionable. Visual elements are restrained, using color only to denote meaning or hierarchy rather than for pure decoration.

## Colors

The palette is rooted in "Trustworthy Blue" to evoke stability and professionalism. 

- **Primary (#1E40AF):** Used for core branding, primary actions, and active navigation states.
- **Success (#10B981):** A "Medical Green" reserved for positive health outcomes, online statuses, and completed tasks.
- **Warning/Tertiary (#F59E0B):** A soft amber for pending actions or low-priority alerts.
- **Neutrals:** A range of professional grays (#F8FAFC to #0F172A) provides the structural framework for borders, backgrounds, and body text, ensuring high contrast and accessibility (WCAG AA compliant).
- **Surface:** The default background is a clean white (#FFFFFF) to maintain a sterile, professional environment.

## Typography

This design system utilizes **Inter** for all typographic roles. Inter’s tall x-height and clear letterforms are ideal for data-heavy admin panels where legibility is paramount.

- **Hierarchies:** Use `display-lg` exclusively for key dashboard metrics (e.g., total patient count). Use `headline-lg` for page titles.
- **Readability:** `body-md` is the standard for patient notes and records, while `body-sm` is used for secondary metadata.
- **Labels:** Use `label-caps` for table headers and section categorizers to distinguish them from actionable data.

## Layout & Spacing

The layout follows a **Fluid Grid** model with a standard 12-column system for desktop views. 

- **Grid:** On desktop, use a 24px gutter. The layout utilizes a fixed-width sidebar (280px) with a fluid content area.
- **Rhythm:** An 8px base unit (0.5rem) governs all spatial relationships. Padding within cards and containers should default to `lg` (24px) to ensure information doesn't feel cramped.
- **Responsiveness:**
  - **Desktop (1280px+):** 12 columns, 32px margins.
  - **Tablet (768px - 1279px):** 6 columns, 24px margins, sidebar collapses to icons.
  - **Mobile (<767px):** 2 columns, 16px margins, vertical stack for all cards and metrics.

## Elevation & Depth

Visual hierarchy is established through **Tonal Layers** supplemented by **Ambient Shadows**. 

- **Background:** The base canvas uses a very light gray (#F1F5F9).
- **Surface:** Cards and containers are pure white (#FFFFFF) to pop against the background.
- **Shadows:** Use extremely diffused, low-opacity shadows (Blur: 12px, Y: 4px, Color: rgba(15, 23, 42, 0.08)) to indicate interactivity on hover for cards.
- **Dividers:** Use 1px borders (#E2E8F0) instead of shadows for separating content within a single container to maintain a clean, "flat" medical look.

## Shapes

The design system uses a **Soft** shape language (roundedness level 1). This provides a professional yet approachable feel.

- **Small elements:** Buttons, input fields, and checkboxes use a 0.25rem (4px) radius.
- **Medium elements:** Status badges and chips use a 0.5rem (8px) radius or full pill-shape for badges.
- **Large elements:** Dashboard cards and modal containers use a 0.75rem (12px) radius to soften the edges of the high-density layout.

## Components

### Buttons
- **Primary:** Solid #1E40AF with white text. 40px height for standard actions.
- **Secondary:** Outline #E2E8F0 with #1E40AF text. Used for "Cancel" or "View Details."
- **Ghost:** No border or background; color #64748B. Used for utility actions in table rows.

### Status Badges (Chips)
- **Online/Success:** Background #DCFCE7, text #10B981.
- **Offline/Error:** Background #FEE2E2, text #EF4444.
- **Pending:** Background #FEF3C7, text #D97706.
- *Styling:* Pill-shaped (rounded-full) with `label-caps` typography.

### Input Fields
- **Default:** 1px solid #CBD5E1 border, white background. 
- **Focus:** 2px solid #1E40AF with a subtle blue outer glow.
- **Labels:** Positioned above the field in `body-sm` bold.

### Structured Settings Cards
- **Header:** 1px bottom border (#F1F5F9) separating title from content.
- **Sections:** Group related settings using subtle background tints or vertical spacing.
- **Footer:** Right-aligned actions (Save/Cancel) housed in a slightly darker gray footer area (#F8FAFC).

### Lists & Tables
- **Rows:** 56px minimum height. Zebra striping is discouraged; use subtle 1px dividers.
- **Hover:** Apply #F8FAFC background color to the entire row to indicate interactivity.