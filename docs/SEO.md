# Search Engine Optimization (SEO) Architecture

`vision-mission` is architected as an SEO-first portal, using automated linking networks, programmatic landings, structural schemas, and IndexNow notifications to maximize organic crawl frequencies and search indexing.

---

## 1. Programmatic Landing Landing Engines

Rather than static lists, directories are generated dynamically to target specific high-intent search queries:
* **Administrative Landers**: `/results`, `/admit-cards`, `/answer-keys`, `/syllabus`, `/cutoffs` dynamically lists posts of these specific types.
* **Geographical Landers**:
  * State Level: `/jobs/state/{state_slug}` (Lists central and state-level openings inside a specific state).
  * District Level: `/jobs/state/{state_slug}/{district_slug}` (Deep geographic queries targeting regional posts).
* **Category Landers**: `/jobs/railway`, `/jobs/banking`, `/jobs/ssc` etc.

---

## 2. Automated Internal Linking Network

Managed via [InternalLinkingService](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Domains/Jobs/Services/InternalLinkingService.php) and warmed via console command:
* **Vocab Injections**: Description and syllabus blocks are crawled on save to find state names, category labels, or department codes. Found terms are converted into `<a href="...">` anchor tags pointing to their respective programmatic landing pages.
* **Timelines and Timestamps**: Link packages on detail pages cross-relate admit cards, answer keys, and results back to their parent job posts, distributing PageRank down the crawl tree.
* **Robots Middleware**: The `internal_linking` middleware applies appropriate crawler directives (e.g. `X-Robots-Tag: index, follow` with strict HTTP caching rules).

---

## 3. Structured Data & Schema Markup

Managed via [SchemaService](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Domains/Jobs/Services/SchemaService.php):
* **JobPosting Schema**: Outputs complete schema maps (JSON-LD format) for job detail pages, including `title`, `description`, `datePosted`, `validThrough` (deadline date), `hiringOrganization`, `jobLocation` (state/district constraints), and base `salary` (min/max bounds).
* **FAQPage & Breadcrumbs**: Generates breadcrumbs hierarchy schemas to enable rich search result snippets.

---

## 4. XML Sitemap Architecture

Managed by [SitemapController](file:///C:/xampp_8.2.12/htdocs/vision-mission/routes/web.php):
* **Index sitemap**: `/sitemap.xml` aggregates child sitemaps.
* **Section sitemaps**:
  * Pages sitemap: `/sitemaps/sitemap-pages.xml` (landing pages, categories).
  * Jobs sitemap: `/sitemaps/sitemap-jobs.xml` (lists all active, published job posts).
  * Images / Videos / FAQs sitemap modules: Context-specific crawl paths.
  * Google News Compliant Sitemap: `/news-sitemap.xml` lists newly published or updated job recruitments within the last 48 hours for instant Google News inclusion.

---

## 5. Search Engine Notification: IndexNow

* **Action Trigger**: [JobPostObserver](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Observers/JobPostObserver.php) listens for newly published posts or updates.
* **Background Job**: Automatically dispatches [SubmitToIndexNow](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Jobs/SubmitToIndexNow.php) to queue pings containing the url payload to IndexNow hubs (Bing, Yandex, etc.).
* **Key Verification Route**: `/{key}.txt` serves verification codes matching the active IndexNow credentials dynamically.
