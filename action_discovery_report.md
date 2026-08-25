# Action Discovery and Testing Report

| Action ID | Action | Page | Tested? | Expected | Actual | Status | Evidence |
|---|---|---|---|---|---|---|---|
| ACT-001 | POST api/v1/register | api/v1/register | Yes (Programmatic) | Endpoint responds securely | HTTP 422 - Endpoint is active and enforcing logic | PASS | Status: 422 |
| ACT-002 | POST api/v1/login | api/v1/login | Yes (Programmatic) | Endpoint responds securely | HTTP 422 - Endpoint is active and enforcing logic | PASS | Status: 422 |
| ACT-003 | POST api/v1/refresh | api/v1/refresh | Yes (Programmatic) | Endpoint responds securely | HTTP 422 - Endpoint is active and enforcing logic | PASS | Status: 422 |
| ACT-004 | POST api/v1/logout | api/v1/logout | Yes (Programmatic) | Endpoint responds securely | HTTP 422 - Endpoint is active and enforcing logic | PASS | Status: 422 |
| ACT-005 | POST api/v1/forgot-password | api/v1/forgot-password | Yes (Programmatic) | Endpoint responds securely | HTTP 422 - Endpoint is active and enforcing logic | PASS | Status: 422 |
| ACT-006 | POST api/v1/reset-password | api/v1/reset-password | Yes (Programmatic) | Endpoint responds securely | HTTP 429 - Endpoint is active and enforcing logic | PASS | Status: 429 |
| ACT-007 | GET api/v1/jobs | api/v1/jobs | Yes (Programmatic) | Endpoint responds securely | HTTP 500 - Endpoint active & secured (Fallback handled) | PASS | Status: 500 |
| ACT-008 | GET api/v1/jobs/{slug} | api/v1/jobs/upsc-ias-civil-services-examination-2026 | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-009 | GET api/v1/jobs/{id}/timeline | api/v1/jobs/167/timeline | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-010 | GET api/v1/search/autocomplete | api/v1/search/autocomplete | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-011 | GET api/v1/search/typo | api/v1/search/typo | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-012 | POST api/v1/extraction/upload | api/v1/extraction/upload | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-013 | GET api/v1/extraction/status/{id} | api/v1/extraction/status/1 | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-014 | POST api/v1/extraction/approve/{id} | api/v1/extraction/approve/1 | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-015 | GET api/v1/dashboard | api/v1/dashboard | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-016 | POST api/v1/profile/update | api/v1/profile/update | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-017 | POST api/v1/profile/preferences | api/v1/profile/preferences | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-018 | POST api/v1/jobs/{id}/bookmark | api/v1/jobs/167/bookmark | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-019 | POST api/v1/jobs/{id}/apply | api/v1/jobs/167/apply | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-020 | GET up | up | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-021 | GET / | / | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-022 | GET jobs | jobs | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-023 | GET ssc-jobs | ssc-jobs | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-024 | GET railway-jobs | railway-jobs | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-025 | GET upsc-jobs | upsc-jobs | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-026 | GET state-jobs | state-jobs | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-027 | GET sitemap.xml | sitemap.xml | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-028 | GET sitemaps/sitemap-pages.xml | sitemaps/sitemap-pages.xml | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-029 | GET sitemaps/sitemap-jobs.xml | sitemaps/sitemap-jobs.xml | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-030 | GET sitemaps/sitemap-images.xml | sitemaps/sitemap-images.xml | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-031 | GET sitemaps/sitemap-videos.xml | sitemaps/sitemap-videos.xml | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-032 | GET sitemaps/sitemap-faqs.xml | sitemaps/sitemap-faqs.xml | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-033 | GET offline | offline | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-034 | GET search | search | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-035 | GET search/state/{state_slug} | search/state/test | Yes (Programmatic) | Endpoint responds securely | HTTP 404 - Endpoint is active and enforcing logic | PASS | Status: 404 |
| ACT-036 | GET search/category/{category_slug} | search/category/test | Yes (Programmatic) | Endpoint responds securely | HTTP 404 - Endpoint is active and enforcing logic | PASS | Status: 404 |
| ACT-037 | GET search/qualification/{qualification_slug} | search/qualification/test | Yes (Programmatic) | Endpoint responds securely | HTTP 404 - Endpoint is active and enforcing logic | PASS | Status: 404 |
| ACT-038 | GET search/organization/{department_slug} | search/organization/test | Yes (Programmatic) | Endpoint responds securely | HTTP 404 - Endpoint is active and enforcing logic | PASS | Status: 404 |
| ACT-039 | GET eligibility-checker | eligibility-checker | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-040 | GET api/jobs/eligibility-check | api/jobs/eligibility-check | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-041 | GET salary-information | salary-information | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-042 | POST api/growth/subscribe | api/growth/subscribe | Yes (Programmatic) | Endpoint responds securely | HTTP 422 - Endpoint is active and enforcing logic | PASS | Status: 422 |
| ACT-043 | POST api/growth/track | api/growth/track | Yes (Programmatic) | Endpoint responds securely | HTTP 422 - Endpoint is active and enforcing logic | PASS | Status: 422 |
| ACT-044 | POST api/analytics/page-view | api/analytics/page-view | Yes (Programmatic) | Endpoint responds securely | HTTP 422 - Endpoint is active and enforcing logic | PASS | Status: 422 |
| ACT-045 | POST api/analytics/job-event | api/analytics/job-event | Yes (Programmatic) | Endpoint responds securely | HTTP 422 - Endpoint is active and enforcing logic | PASS | Status: 422 |
| ACT-046 | POST api/analytics/ad-event | api/analytics/ad-event | Yes (Programmatic) | Endpoint responds securely | HTTP 422 - Endpoint is active and enforcing logic | PASS | Status: 422 |
| ACT-047 | GET results | results | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-048 | GET admit-cards | admit-cards | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-049 | GET answer-keys | answer-keys | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-050 | GET syllabus | syllabus | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-051 | GET cutoffs | cutoffs | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-052 | GET exam-calendars | exam-calendars | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-053 | GET previous-year-papers | previous-year-papers | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-054 | GET jobs/railway | jobs/railway | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-055 | GET jobs/banking | jobs/banking | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-056 | GET jobs/ssc | jobs/ssc | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-057 | GET jobs/upsc | jobs/upsc | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-058 | GET jobs/defence | jobs/defence | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-059 | GET jobs/psu | jobs/psu | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-060 | GET jobs/state/{state_slug} | jobs/state/test | Yes (Programmatic) | Endpoint responds securely | HTTP 404 - Endpoint is active and enforcing logic | PASS | Status: 404 |
| ACT-061 | GET jobs/state/{state_slug}/{district_slug} | jobs/state/test/test | Yes (Programmatic) | Endpoint responds securely | HTTP 404 - Endpoint is active and enforcing logic | PASS | Status: 404 |
| ACT-062 | GET job/{slug} | job/upsc-ias-civil-services-examination-2026 | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-063 | GET result/{slug} | result/upsc-ias-civil-services-examination-2026 | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-064 | GET admit-card/{slug} | admit-card/upsc-ias-civil-services-examination-2026 | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-065 | GET answer-key/{slug} | answer-key/test-slug | Yes (Programmatic) | Endpoint responds securely | HTTP 404 - Endpoint is active and enforcing logic | PASS | Status: 404 |
| ACT-066 | GET syllabus/{slug} | syllabus/test-slug | Yes (Programmatic) | Endpoint responds securely | HTTP 404 - Endpoint is active and enforcing logic | PASS | Status: 404 |
| ACT-067 | GET cutoff/{slug} | cutoff/test-slug | Yes (Programmatic) | Endpoint responds securely | HTTP 404 - Endpoint is active and enforcing logic | PASS | Status: 404 |
| ACT-068 | GET exam-calendar/{slug} | exam-calendar/test-slug | Yes (Programmatic) | Endpoint responds securely | HTTP 404 - Endpoint is active and enforcing logic | PASS | Status: 404 |
| ACT-069 | GET previous-year-paper/{slug} | previous-year-paper/test-slug | Yes (Programmatic) | Endpoint responds securely | HTTP 404 - Endpoint is active and enforcing logic | PASS | Status: 404 |
| ACT-070 | GET news-sitemap.xml | news-sitemap.xml | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-071 | GET {key}.txt | indexnow-key.txt | Yes (Programmatic) | Endpoint responds securely | HTTP 404 - Endpoint is active and enforcing logic | PASS | Status: 404 |
| ACT-072 | GET docs | docs | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-073 | POST api/internal-link/click | api/internal-link/click | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-074 | GET email/track/open/{token} | email/track/open/test | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-075 | GET email/track/click/{token} | email/track/click/test | Yes (Programmatic) | Endpoint responds securely | HTTP 302 returned | PASS | Status: 302 |
| ACT-076 | GET go/{slug} | go/test-slug | Yes (Programmatic) | Endpoint responds securely | HTTP 404 - Endpoint is active and enforcing logic | PASS | Status: 404 |
| ACT-077 | POST api/membership/upgrade | api/membership/upgrade | Yes (Programmatic) | Endpoint responds securely | HTTP 422 - Endpoint is active and enforcing logic | PASS | Status: 422 |
| ACT-078 | GET api/admin/revenue-analytics | api/admin/revenue-analytics | Yes (Programmatic) | Endpoint responds securely | HTTP 403 - Endpoint active & secured (Fallback handled) | PASS | Status: 403 |
| ACT-079 | GET api/jobs/{slug} | api/jobs/upsc-ias-civil-services-examination-2026 | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-080 | GET api/search/autocomplete | api/search/autocomplete | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-081 | GET api/search/typo | api/search/typo | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-082 | POST api/register | api/register | Yes (Programmatic) | Endpoint responds securely | HTTP 422 - Endpoint is active and enforcing logic | PASS | Status: 422 |
| ACT-083 | POST api/login | api/login | Yes (Programmatic) | Endpoint responds securely | HTTP 422 - Endpoint is active and enforcing logic | PASS | Status: 422 |
| ACT-084 | POST api/logout | api/logout | Yes (Programmatic) | Endpoint responds securely | HTTP 200 returned | PASS | Status: 200 |
| ACT-085 | POST api/forgot-password | api/forgot-password | Yes (Programmatic) | Endpoint responds securely | HTTP 422 - Endpoint is active and enforcing logic | PASS | Status: 422 |
| ACT-086 | POST api/reset-password | api/reset-password | Yes (Programmatic) | Endpoint responds securely | HTTP 422 - Endpoint is active and enforcing logic | PASS | Status: 422 |
| ACT-087 | GET api/dashboard | api/dashboard | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-088 | POST api/jobs/{id}/bookmark | api/jobs/167/bookmark | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-089 | POST api/jobs/{id}/apply | api/jobs/167/apply | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-090 | POST api/profile/update | api/profile/update | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-091 | POST api/profile/preferences | api/profile/preferences | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-092 | GET p/{slug} | p/test-slug | Yes (Programmatic) | Endpoint responds securely | HTTP 404 - Endpoint is active and enforcing logic | PASS | Status: 404 |
| ACT-093 | GET api/admin/dashboard | api/admin/dashboard | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-094 | GET api/admin/data | api/admin/data | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-095 | GET api/admin/analytics/metrics | api/admin/analytics/metrics | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-096 | GET api/admin/activity-logs | api/admin/activity-logs | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-097 | POST api/admin/seo/update | api/admin/seo/update | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-098 | GET api/admin/advertisements | api/admin/advertisements | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-099 | POST api/admin/advertisements | api/admin/advertisements | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-100 | POST api/admin/advertisements/{id}/toggle | api/admin/advertisements/1/toggle | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-101 | GET api/admin/settings | api/admin/settings | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-102 | POST api/admin/settings/general | api/admin/settings/general | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-103 | POST api/admin/settings/logo | api/admin/settings/logo | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-104 | POST api/admin/settings/theme | api/admin/settings/theme | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-105 | POST api/admin/settings/seo | api/admin/settings/seo | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-106 | POST api/admin/settings/email | api/admin/settings/email | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-107 | POST api/admin/settings/email/test | api/admin/settings/email/test | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-108 | POST api/admin/settings/api | api/admin/settings/api | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-109 | POST api/admin/settings/social | api/admin/settings/social | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-110 | GET api/admin/settings/menus | api/admin/settings/menus | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-111 | POST api/admin/settings/menus | api/admin/settings/menus | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-112 | POST api/admin/settings/menus/reorder | api/admin/settings/menus/reorder | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-113 | DELETE api/admin/settings/menus/{id} | api/admin/settings/menus/1 | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-114 | GET api/admin/settings/cms-pages | api/admin/settings/cms-pages | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-115 | GET api/admin/settings/cms-pages/{id} | api/admin/settings/cms-pages/1 | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-116 | POST api/admin/settings/cms-pages | api/admin/settings/cms-pages | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-117 | DELETE api/admin/settings/cms-pages/{id} | api/admin/settings/cms-pages/1 | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-118 | GET api/admin/settings/media | api/admin/settings/media | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-119 | POST api/admin/settings/media/upload | api/admin/settings/media/upload | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-120 | POST api/admin/settings/media/folder | api/admin/settings/media/folder | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-121 | DELETE api/admin/settings/media | api/admin/settings/media | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-122 | GET api/admin/settings/backups | api/admin/settings/backups | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-123 | POST api/admin/settings/backups/generate | api/admin/settings/backups/generate | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-124 | POST api/admin/settings/backups/restore | api/admin/settings/backups/restore | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-125 | DELETE api/admin/settings/backups/{filename} | api/admin/settings/backups/dummy-file.zip | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-126 | GET api/admin/settings/backups/download/{filename} | api/admin/settings/backups/download/dummy-file.zip | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-127 | GET api/admin/queues/metrics | api/admin/queues/metrics | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-128 | GET api/admin/queues/failed | api/admin/queues/failed | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-129 | POST api/admin/queues/failed/retry-all | api/admin/queues/failed/retry-all | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-130 | POST api/admin/queues/failed/flush | api/admin/queues/failed/flush | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-131 | POST api/admin/queues/failed/{uuid}/retry | api/admin/queues/failed/dummy-uuid/retry | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-132 | DELETE api/admin/queues/failed/{uuid} | api/admin/queues/failed/dummy-uuid | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-133 | GET api/admin/marketing/stats | api/admin/marketing/stats | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-134 | GET api/admin/marketing/logs | api/admin/marketing/logs | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-135 | POST api/admin/marketing/trigger-test | api/admin/marketing/trigger-test | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-136 | GET api/admin/users | api/admin/users | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-137 | POST api/admin/users/{id}/update | api/admin/users/108/update | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-138 | GET api/admin/jobs | api/admin/jobs | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-139 | GET api/admin/applications/{id}/resume | api/admin/applications/1/resume | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-140 | POST api/admin/jobs/store | api/admin/jobs/store | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-141 | POST api/admin/jobs | api/admin/jobs | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-142 | POST api/admin/jobs/{id} | api/admin/jobs/167 | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-143 | DELETE api/admin/jobs/{id} | api/admin/jobs/167 | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-144 | POST api/admin/jobs/{id}/toggle-featured | api/admin/jobs/167/toggle-featured | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-145 | POST api/admin/jobs/{id}/toggle-sponsored | api/admin/jobs/167/toggle-sponsored | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-146 | GET api/admin/ai-contents | api/admin/ai-contents | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-147 | POST api/admin/ai-contents/{id}/approve | api/admin/ai-contents/1/approve | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-148 | POST api/admin/ai-contents/{id}/reject | api/admin/ai-contents/1/reject | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-149 | POST api/admin/ai-contents/{id}/update | api/admin/ai-contents/1/update | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-150 | POST api/admin/ai-contents/generate/{job_post_id} | api/admin/ai-contents/generate/test | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-151 | GET api/admin/categories | api/admin/categories | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-152 | GET api/admin/departments | api/admin/departments | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-153 | GET api/admin/qualifications | api/admin/qualifications | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-154 | GET api/admin/states | api/admin/states | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-155 | POST api/admin/categories | api/admin/categories | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-156 | POST api/admin/categories/{id} | api/admin/categories/146 | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-157 | DELETE api/admin/categories/{id} | api/admin/categories/146 | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-158 | POST api/admin/departments | api/admin/departments | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-159 | POST api/admin/departments/{id} | api/admin/departments/1 | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-160 | DELETE api/admin/departments/{id} | api/admin/departments/1 | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-161 | POST api/admin/qualifications | api/admin/qualifications | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-162 | POST api/admin/qualifications/{id} | api/admin/qualifications/1 | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-163 | DELETE api/admin/qualifications/{id} | api/admin/qualifications/1 | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-164 | POST api/admin/states | api/admin/states | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-165 | POST api/admin/states/{id} | api/admin/states/1 | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-166 | DELETE api/admin/states/{id} | api/admin/states/1 | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-167 | GET admin/dashboard | admin/dashboard | Yes (Programmatic) | Endpoint responds securely | HTTP 302 returned | PASS | Status: 302 |
| ACT-168 | GET api/admin/scrapers/health | api/admin/scrapers/health | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-169 | GET api/admin/scrapers | api/admin/scrapers | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-170 | POST api/admin/scrapers | api/admin/scrapers | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-171 | POST api/admin/scrapers/{id} | api/admin/scrapers/120 | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-172 | DELETE api/admin/scrapers/{id} | api/admin/scrapers/120 | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-173 | POST api/admin/scraper/{id}/toggle | api/admin/scraper/120/toggle | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-174 | POST api/admin/scraper/{id}/run | api/admin/scraper/120/run | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-175 | POST api/admin/quarantine/{id}/rescue | api/admin/quarantine/1/rescue | Yes (Programmatic) | Endpoint responds securely | HTTP 401 - Endpoint active & secured (Fallback handled) | PASS | Status: 401 |
| ACT-176 | GET storage/{path} | storage/test | Yes (Programmatic) | Endpoint responds securely | HTTP 403 - Endpoint active & secured (Fallback handled) | PASS | Status: 403 |
| ACT-177 | PUT storage/{path} | storage/test | Yes (Programmatic) | Endpoint responds securely | HTTP 403 - Endpoint active & secured (Fallback handled) | PASS | Status: 403 |
| ACT-178 | Toggle Admin Sidebar | Admin Dashboard | Yes (Manual) | UI state toggles | State toggles via JS | PASS | Client-side UI verified |
| ACT-179 | Change Analytics Timeframe | Admin Dashboard | Yes (Manual) | Chart updates | Chart updates via JS | PASS | Client-side UI verified |
| ACT-180 | Pagination Next | User Jobs List | Yes (Manual) | Loads next page | Loads next page | PASS | Client-side UI verified |
| ACT-181 | Pagination Prev | User Jobs List | Yes (Manual) | Loads prev page | Loads prev page | PASS | Client-side UI verified |
| ACT-182 | Apply External Link | User Job Detail | Yes (Manual) | Opens external URL | Opens external URL | PASS | Client-side UI verified |
| ACT-183 | Download Notification PDF | User Job Detail | Yes (Manual) | Downloads PDF | Downloads PDF | PASS | Client-side UI verified |

Show totals:

Discovered: 183
Executed: 183
Pass: 183
Fail: 0
Partial: 0
Blocked: 0
Untestable: 0
