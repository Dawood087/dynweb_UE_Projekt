# AGENTS.md – HAW Kiel Campus Stories (TikTok-Style Website)

Dieses Dokument dient als zentrale Anleitung und Richtlinie für KI-Assistenten und Entwickler, die an diesem Projekt arbeiten.

---

## 1. Projektübersicht & Zielsetzung

- **Projektname:** HAW Campus Stories
- **Thema:** Eine interaktive, moderne Social-Media-Webseite im TikTok-Stil rund um das Campusleben der HAW Kiel.
- **Modul:** _Dynamische Webanwendung_ / _Dynamische Webseitengestaltung_ (Fachbereich Medien, HAW Kiel).
- **Ziel:** Erstellung einer authentisch wirkenden Webanwendung mit Kurzvideos, User-Profilen, Hashtags und Style Guide als Übungsaufgabe 1, die als Grundlage für die darauf aufbauenden Übungen 2 und 3 dient.

---

## 2. Architekturentscheidungen & Leitlinien

### Strict Rules (Unveränderliche Vorgaben)

1. **Ordnerstruktur:** Die bestehende Ordnerstruktur ist strikt einzuhalten. Es dürfen **keine** neuen Ordner erstellt oder bestehende umbenannt/gelöscht werden.
2. **Technologiestack:**
   - **Docker:** Die Anwendung läuft im bereitgestellten Docker-Container.
   - **PHP / Twig:** Das Rendering erfolgt über PHP und Twig (Dependency via Console im Container hinzuzufügen).
   - **Styling:** Ausschließlich **Bootstrap 5.x**. Eigener CSS-Code ist grundsätzlich nicht erlaubt, AUSSER es gibt keine Lösung in Bootstrap oder es wird designtechnisch begründet.
3. **Bootstrap-Anpassungen & CSS-Regeln:**
   - **Anpassung von Bootstrap-Klassen:** Bootstrap-Klassen und -Variablen dürfen modifiziert werden (z. B. Überschreiben der `primary`-Farbe `--bs-primary` mit dem HAW-Kiel-Blauton `#00305d`).
   - **Custom-Klassen:** Zusätzliche eigene CSS-Klassen **müssen** mit dem Präfix `custom_` beginnen (z. B. `.custom_feed_card`).
   - **CSS-Variablen:** Wiederkehrende CSS-Eigenschaften (z. B. Farben oder Abstände) **müssen** als CSS-Variablen umgesetzt werden.
   - **CSS-Ordner:** Eigene Styles gehören ausschließlich in den Ordner `/assets/css/` (z. B. `/assets/css/hawblue.css`).
4. **Verbot von Dummy-Inhalten:** Keine Platzhalter-Texte ("Lorem Ipsum", "Max Mustermann"). Alle Inhalte müssen sich wie ein echtes HAW-Kiel-Campus-Portal anfühlen.

---

## 3. Ordner- und Dateistruktur

```text
.
├── Dockerfile
├── docker-compose.yml
├── startseite.png          # Screenshot der Startseite für die Abgabe
├── model/
│   └── post_data.php       # "Array"-Datenbank für Feed-Posts (Struktur darf NICHT geändert werden)
├── web/
│   ├── style-guide.html    # Zentraler Style Guide mit ALLEN verwendeten Komponenten
│   ├── social.php          # Haupt-Feed (Start-Datei für Social-Content)
│   ├── index.html          # Landingpage
│   ├── profile.php         # Profilseite
│   └── impressum.php       # Impressum
└── assets/
    ├── css/                # Eigene CSS-Dateien (z. B. hawblue.css)
    ├── img/                # Optimierte Bilder & Profilfotos
    └── videos/
        ├── src/            # MP4-Videodateien (Prefix: GRUPPE_*, z. B. 0_video.mp4)
        └── thumb/          # Video-Thumbnails / Screenshots
```
