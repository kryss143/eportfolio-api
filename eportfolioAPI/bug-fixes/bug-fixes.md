# Bug File Discovery Log — 2026-09-28

Scanned root: `/home/kryss/Documents/GitHub/eportfolio-api/eportfolioAPI`

Found 27 file(s) mentioning "bug"/"bugs":

## `BUGS-2026-09-25-v2.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 1: # Bug Audit v2 — 2026-09-25 (post-fix regression sweep, eportfolioAPI excluding .claude)
- line 4: **Method:** bug-finder skill — static hunt across the changed surface since the v1 report (probe, migration command, ...
- line 24: **Impact:** every `/api/v1/blogs/{slug}` and `/api/v1/projects/{id}` request paid an extra server-selection round tri...

## `docs/mongodb-migration.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 14: - `App\Support\MongoProbe` — real `ping()`-based availability probe (bug #2

## `vendor/dragonmantank/cron-expression/CHANGELOG.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 13: - Fixed bug in Next Execution Time by sorting minutes properly (#160, thank you https://github.com/imyip)
- line 155: - Fixed bug where single number ranges were allowed (ex: `1/10`)
- line 229: - Fixed looping bug for PHP 7 when determining the last specified weekday of a month

## `vendor/symfony/process/CHANGELOG.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 115: * added ProcessUtils::escapeArgument() to fix the bug in escapeshellarg() function on Windows

## `vendor/fakerphp/faker/CHANGELOG.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 41: - Fixed some Hungarian naming bugs (#451)
- line 42: - Fixed bug where the NL-BE VAT generation was incorrect (#455)

## `vendor/guzzlehttp/uri-template/CHANGELOG.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 93: - Fixed some bugs when parts ofs values are not strings

## `vendor/guzzlehttp/psr7/CHANGELOG.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 316: - Bug MultipartStream no `uri` metadata
- line 317: - Bug MultipartStream with filename for `data://` streams
- line 567: - A bug in validating request methods by making it more permissive.

## `vendor/guzzlehttp/guzzle/CHANGELOG.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 762: * Bug fix: Parsing 0 epoch expiry times in cookies [#2014](https://github.com/guzzle/guzzle/pull/2014)
- line 764: * Bug fix: Malformed domain that contains a "/" [#1999](https://github.com/guzzle/guzzle/pull/1999)
- line 765: * Bug fix: Undefined offset when a cookie has no first key-value pair [#1998](https://github.com/guzzle/guzzle/pull/1...
- line 767: * Bug fix: Support empty headers [#1915](https://github.com/guzzle/guzzle/pull/1915)
- line 768: * Bug fix: Ignore case during header modifications [#1916](https://github.com/guzzle/guzzle/pull/1916)
- line 781: * Bug fix: PHP 7.x fixes [#1685](https://github.com/guzzle/guzzle/pull/1685), [#1686](https://github.com/guzzle/guzzl...
- line 788: * Bug fix: Fill `CURLOPT_CAPATH` and `CURLOPT_CAINFO` properly [#1684](https://github.com/guzzle/guzzle/pull/1684)
- line 811: * Fixing timeout bug with StreamHandler:
- line 822: * Bug fix: Fix sleep calculation when waiting for delayed requests.
- line 826: * Bug fix: defer sink stream opening in StreamHandler.
- line 828: * Bug fix: do not attempt to escape cookie values.
- line 832: * Bug fix: rewind seekable request bodies before dispatching to cURL.
- line 834: * Bug fix: provide an empty string to `http_build_query` for HHVM workaround.
- line 840: * Bug fix: Proxy::wrapSync() now correctly proxies to the appropriate handler
- line 844: * Bug fix: setting verify to false in the StreamHandler now disables peer
- line 850: * Bug fix: fixed regression where MockHandler was not using `sink`.
- line 866: * Bug fix: Now correctly parsing `=` inside of quotes in Cookies.
- line 868: * Bug fix: Cusotm cURL options now correctly override curl options of the
- line 870: * Bug fix: Content-Type header is now added when using an explicitly provided
- line 872: * Bug fix: Now ignoring Set-Cookie headers that have no name.
- line 873: * Bug fix: Reason phrase is no longer cast to an int in some cases in the
- line 875: * Bug fix: Remove the Authorization header when redirecting if the Host
- line 877: * Bug fix: Cookie path matching fixes
- line 879: * Bug fix: Fixing the cURL `body_as_string` setting
- line 881: * Bug fix: quotes are no longer stripped when parsing cookies.
- line 883: * Bug fix: `form_params` and `query` now always uses the `&` separator.
- line 885: * Bug fix: Adding a Content-Length to PHP stream wrapper requests if not set.
- line 909: * Fixed a bug with serializing the `query` request option where the `&`
- line 945: * Fixed a bug in which multiple headers using different casing would overwrite
- line 1223: * Fixed a bug where multipart/form-data POST fields were not correctly
- line 1241: * Fixed a bug that caused multi-part POST requests with more than one field to
- line 1341: * Bug: Always using GET requests when redirecting from a 303 response
- line 1342: * Bug: CURLOPT_SSL_VERIFYHOST is now correctly set to false when setting `$certificateAuthority` to false in
- line 1344: * Bug: RedirectPlugin now uses strict RFC 3986 compliance when combining a base URL with a relative URL
- line 1345: * Bug: The body of a request can now be set to `"0"`
- line 1367: * Fixed a bug that was encountered when parsing empty header parameters
- line 1379: * Bug fix: 0 is now an allowed value in a description parameter that has a default value (#430)
- line 1380: * Bug fix: SchemaFormatter now returns an integer when formatting to a Unix timestamp
- line 1382: * Bug fix: Cleaned up and fixed URL dot segment removal to properly resolve internal dots
- line 1395: * Bug fix: ChunkedIterator can now properly chunk a \Traversable as well as an \Iterator.
- line 1396: * Bug fix: FilterIterator now relies on `\Iterator` instead of `\Traversable`.
- line 1397: * Bug fix: Gracefully handling malformed responses in RequestMediator::writeResponseBody()
- line 1398: * Bug fix: Replaced call to canCache with canCacheRequest in the CallbackCanCacheStrategy of the CachePlugin
- line 1399: * Bug fix: Visiting XML attributes first before visiting XML children when serializing requests
- line 1400: * Bug fix: Properly parsing headers that contain commas contained in quotes
- line 1401: * Bug fix: mimetype guessing based on a filename is now case-insensitive
- line 1405: * Bug fix: Properly URL encoding paths when using the PHP-only version of the UriTemplate expander
- line 1407: * Bug fix: Cookie domains are now matched correctly according to RFC 6265
- line 1409: * Bug fix: GET parameters are now used when calculating an OAuth signature
- line 1410: * Bug fix: Fixed an issue with cache revalidation where the If-None-Match header was being double quoted
- line 1420: * Bug fix: Setting default options on a client now works
- line 1421: * Bug fix: Setting options on HEAD requests now works. See #352
- line 1422: * Bug fix: Moving stream factory before send event to before building the stream. See #353
- line 1423: * Bug fix: Cookies no longer match on IP addresses per RFC 6265
- line 1424: * Bug fix: Correctly parsing header parameters that are in `<>` and quotes
- line 1454: * Fixed a bug in `Guzzle\Http\Message\Header\Link::addLink()`
- line 1556: * Bug: Fixed a regression so that request responses are parsed only once per oncomplete event rather than multiple times
- line 1557: * Bug: Better cleanup of one-time events across the board (when an event is meant to fire once, it will now remove
- line 1559: * Bug: `Guzzle\Log\MessageFormatter` now properly writes "total_time" and "connect_time" values
- line 1560: * Bug: Cloning an EntityEnclosingRequest now clones the EntityBody too
- line 1561: * Bug: Fixed an undefined index error when parsing nested JSON responses with a sentAs parameter that reference a
- line 1563: * Bug: All __call() method arguments are now required (helps with mocking frameworks)
- line 1578: * Bug fix: Fixing bug introduced in 3.4.2 where redirect responses are duplicated on the final redirected response
- line 1583: * Bug fix: Stream objects now work correctly with "a" and "a+" modes
- line 1584: * Bug fix: Removing `Transfer-Encoding: chunked` header when a Content-Length is present
- line 1585: * Bug fix: AsyncPlugin no longer forces HEAD requests
- line 1586: * Bug fix: DateTime timezones are now properly handled when using the service description schema formatter
- line 1587: * Bug fix: CachePlugin now properly handles stale-if-error directives when a request to the origin server fails
- line 1599: handles. This greatly simplifies the implementation, fixes a couple bugs, and provides a small performance boost.
- line 1603: * Bug fix: Model names are now properly set even when using $refs
- line 1611: * Bug fix: URLs are now resolved correctly based on https://datatracker.ietf.org/doc/html/rfc3986#section-5.2. #289
- line 1612: * Bug fix: Absolute URLs with a path in a service description will now properly override the base URL. #289
- line 1613: * Bug fix: Parsing a query string with a single PHP array value will now result in an array. #263
- line 1614: * Bug fix: Better normalization of the User-Agent header to prevent duplicate headers. #264.
- line 1615: * Bug fix: Added `number` type to service descriptions.
- line 1616: * Bug fix: empty parameters are removed from an OAuth signature
- line 1617: * Bug fix: Revalidating a cache entry prefers the Last-Modified over the Date header
- line 1618: * Bug fix: Fixed "array to string" error when validating a union of types in a service description
- line 1619: * Bug fix: Removed code that attempted to determine the size of a stream when data is written to the stream
- line 1620: * Bug fix: Not including an `oauth_token` if the value is null in the OauthPlugin.
- line 1621: * Bug fix: Now correctly aggregating successful requests and failed requests in CurlMulti when a redirect occurs.
- line 1642: * Bug fix: Running any filters when parsing response headers with service descriptions
- line 1643: * Bug fix: OauthPlugin fixes to allow for multi-dimensional array signing, and sorting parameters before signing
- line 1644: * Bug fix: Removed the adding of default empty arrays and false Booleans to responses in order to be consistent across
- line 1646: * Bug fix: Removed the possibility of creating configuration files with circular dependencies
- line 1653: * Bug fix: Added 'wb' as a valid write mode for streams
- line 1654: * Bug fix: `Guzzle\Http\Message\Response::json()` now allows scalar values to be returned
- line 1655: * Bug fix: Fixed bug in `Guzzle\Http\Message\Response` where wrapping quotes were stripped from `getEtag()`
- line 1688: * Bug fix: Filters were not always invoked for array service description parameters
- line 1689: * Bug fix: Redirects now use a target response body rather than a temporary response body
- line 1690: * Bug fix: The default exponential backoff BackoffPlugin was not giving when the request threshold was exceeded
- line 1691: * Bug fix: Guzzle now takes the first found value when grabbing Cache-Control directives
- line 1699: * Fixed a bug where redirect responses were not chained correctly using getPreviousResponse()
- line 1714: * Bug: Removing hard dependency on the BackoffPlugin from Guzzle\Http
- line 1715: * Bug: Adding required content-type when JSON request visitor adds JSON to a command
- line 1716: * Bug: Fixing the serialization of a service description with custom data
- line 1740: * Bug: Fixing an infinite recursion bug caused from revalidating with the CachePlugin
- line 1741: * Bug: Response body can now be a string containing "0"
- line 1742: * Bug: Using Guzzle inside of a phar uses system by default but now allows for a custom cacert
- line 1743: * Bug: QueryString::fromString now properly parses query string parameters that contain equal signs
- line 1750: * Bug: Fixed a bug when adding multiple cookies to a request to use the correct glue value
- line 1751: * Bug: Cookies can now be added that have a name, domain, or value set to "0"
- line 1752: * Bug: Using the system cacert bundle when using the Phar
- line 1763: * Bug: Fixed Content-Length parsing of Response factory
- line 1773: * Bug: Fixed an issue with URI templates where null template variables were being expanded
- line 1779: * Added a custom AppendIterator to get around a PHP bug with the `\AppendIterator`
- line 1815: * Bug: Fixed a cookie issue that caused dot prefixed domains to not match where popular browsers did
- line 1819: * Bug: Fixed config file aliases for JSON includes
- line 1820: * Bug: Fixed cookie bug on a request object by using CookieParser to parse cookies on requests
- line 1821: * Bug: Removing the path to a file when sending a Content-Disposition header on a POST upload
- line 1822: * Bug: Hardening request and response parsing to account for missing parts
- line 1823: * Bug: Fixed PEAR packaging
- line 1824: * Bug: Fixed Request::getInfo
- line 1825: * Bug: Fixed cases where CURLM_CALL_MULTI_PERFORM return codes were causing curl transactions to fail
- line 1847: * Bug: Suppressed empty arrays from URI templates
- line 1848: * Bug: Added the missing $options argument from ServiceDescription::factory to enable caching
- line 1855: * Bug: Custom delay time calculations are no longer ignored in the ExponentialBackoffPlugin
- line 1869: * Bug: Fixed a case where empty POST requests were sent as GET requests
- line 1870: * Bug: Fixed a bug in ExponentialBackoffPlugin that caused fatal errors when retrying an EntityEnclosingRequest that ...
- line 1871: * Bug: Setting the response body of a request to null after completing a request, not when setting the state of a req...
- line 1879: * Bug: Query string values set to 0 are no longer dropped from the query string
- line 1880: * Bug: A Collection object is no longer created each time a call is made to `Guzzle\Service\Command\AbstractCommand::...
- line 1881: * Bug: `+` is now treated as an encoded space when parsing query strings
- line 1901: * Bug: URI template variables set to null are no longer expanded
- line 1908: * Bug fix: CachePlugin now only caches GET and HEAD requests by default
- line 1909: * Bug fix: Using header glue when transferring headers over the wire
- line 1924: * Bug: Now allowing colons in a response start-line (e.g. HTTP/1.1 503 Service Unavailable: Back-end server is at cap...
- line 1941: * Bug: Setting the name of each ApiParam when creating through an ApiCommand
- line 1943: * Bug: Changed the default cookie header casing back to 'Cookie'
- line 1961: * Bug: Fixing magic method command calls on clients
- line 1962: * Bug: Email constraint only validates strings
- line 1963: * Bug: Aggregate POST fields when POST files are present in curl handle
- line 1964: * Bug: Fixing default User-Agent header
- line 1965: * Bug: Only appending or prepending parameters in commands if they are specified
- line 1966: * Bug: Not requiring response reason phrases or status codes to match a predefined list of codes
- line 2023: * Fixed a caching bug in the CacheAdapterFactory

## `vendor/guzzlehttp/guzzle/README.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 43: We use GitHub issues only to discuss bugs and new features. For support please refer to:

## `vendor/guzzlehttp/guzzle/UPGRADING.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 1245: #### [BUG] Accept-Encoding header behavior changed unintentionally.
- line 1250: properly handle gzip/deflate compressed responses from the server. In versions affected by this bug this does not hap...

## `vendor/voku/portable-ascii/CHANGELOG.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 105: - fix possible wrong type from "getDataIfExists()" -> e.g. a bug reported where "/data/" was modified

## `vendor/voku/portable-ascii/README.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 447: - Thanks to [PHPStan](https://github.com/phpstan/phpstan) && [Psalm](https://github.com/vimeo/psalm) for really great...

## `vendor/egulias/email-validator/CONTRIBUTING.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 10: When doing a PR to v2 remember that you also have to do the PR port to v3, or tests confirming the bug is not reprodu...
- line 12: 1. Supported version is v3. If you are fixing a bug in v2, please port to v3

## `vendor/brick/math/CHANGELOG.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 13: 🐛 **Bug fixes**
- line 225: 🐛 **Bug fixes**
- line 258: 🐛 **Bug fixes**
- line 378: 🐛 **Bug fix**
- line 388: 🐛 **Bug fixes**
- line 406: 🐛 **Bug fix**
- line 460: This is a maintenance release: no bug fixes, no new features, no breaking changes.
- line 482: 🐛 **Bug fixes**
- line 519: **Bug fix**: `of()` factory methods could fail when passing a `float` in environments using a `LC_NUMERIC` locale wit...
- line 706: Backport of two bug fixes from the 0.5 branch:
- line 726: Backport of two bug fixes from the 0.5 branch:

## `vendor/monolog/monolog/CHANGELOG.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 44: * Fixed `RotatingFileHandler` bug where rotation could sometimes not happen correctly (#1905)
- line 71: * Fixed bug where the previous error handler would not be restored in some cases where StreamHandler fails (#1815)
- line 176: * Fixed `RotatingFileHandler` bug where rotation could sometimes not happen correctly (#1905)
- line 185: * Fixed bug where the previous error handler would not be restored in some cases where StreamHandler fails (#1815)
- line 459: * Fixed HipChatHandler bug where slack dropped messages randomly
- line 462: * Fixed race bug when StreamHandler sometimes incorrectly reported it failed to create a directory
- line 510: * Fixed a few minor bugs
- line 526: * Fixed SlackHandler bug where slack dropped messages randomly
- line 550: * Fixed SwiftMailerHandler bug when sending multiple emails they all had the same id
- line 571: * Fixed bug in the handling of curl failures
- line 669: * Break: the LineFormatter now strips newlines by default because this was a bug, set $allowInlineLineBreaks to true ...
- line 796: * Fixed bug in IE with large response headers and FirePHPHandler

## `vendor/monolog/monolog/README.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 96: ### Submitting bugs and feature requests
- line 98: Bugs and feature request are tracked on [GitHub](https://github.com/Seldaek/monolog/issues)

## `vendor/league/flysystem/SECURITY.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 11: > FYI: There is no bug-bounty program.

## `vendor/league/config/README.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 118: When a new **minor** version (e.g. `1.0` -> `1.1`) is released, the previous one (`1.0`) will continue to receive sec...
- line 120: When a new **major** version is released (e.g. `1.1` -> `2.0`), the previous one (`1.1`) will receive critical bug fi...

## `vendor/league/commonmark/CHANGELOG.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 182: - Fixed issue where having 500,000+ delimiters could trigger a [known segmentation fault issue in PHP's garbage colle...

## `vendor/league/commonmark/README.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 120: [SemVer](http://semver.org/) is followed closely. Minor and patch releases should not introduce breaking changes to t...
- line 126: When a new **minor** version (e.g. `2.0` -> `2.1`) is released, the previous one (`2.0`) will continue to receive sec...
- line 128: When a new **major** version is released (e.g. `1.6` -> `2.0`), the previous one (`1.6`) will receive critical bug fi...
- line 138: If you encounter a bug in the spec, please report it to the [CommonMark] project.  Any resulting fix will eventually ...

## `vendor/hamcrest/hamcrest-php/CONTRIBUTING.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 8: - A reproducible example is required for every bug report, otherwise it will most probably be __closed without warnin...
- line 12: 1. Create your feature addition or a bug fix branch based on __`master`__ branch in your repository's fork.

## `vendor/mockery/mockery/CHANGELOG.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 299: - Fix a bug introduced with previous release, for empty method definition lists (#1009)

## `vendor/mockery/mockery/CONTRIBUTING.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 7: ## Reporting Bugs
- line 10: Please try and report any bugs with a minimal reproducible example, it will make things easier for other
- line 35: * Add the code for your feature or bug
- line 36: * Add some tests for your feature or bug
- line 54: master branch, if it's a bug fix, you want to be targeting a release branch,

## `vendor/filp/whoops/CHANGELOG.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 30: * Fixed bug with PrettyPageHandler "*Calling `getFrameFilters` method on null*" ([#751](https://github.com/filp/whoop...

## `vendor/phpunit/phpunit/SECURITY.md`

Status: ⚪ pending — not yet processed by the bug-fix skill

- line 6: Versions in the Life Support phase only receive changes required for compatibility with new versions of PHP; they do ...
- line 50: I do not operate a bug bounty program.
- line 55: A confirmed security issue is handled like any other bug.
- line 73: I treat a bug as a security issue when a documented, intended use of PHPUnit, or a reasonable extrapolation of it, ca...

## `bugs-reported/pattern-scan.md`

Status: 🟢 fixed — raw scanner output; fully triaged into `AUDIT-app-2026-09-28.md`, whose confirmed findings are all fixed (see `bug-fixes/AUDIT-app-2026-09-28-fixes.md`)

- line 1: # Bug Pattern Scan — 2026-09-28
- line 5: **These are leads, not confirmed bugs.** Each hit needs manual read + the verification protocol in SKILL.md before it...
- line 49: - Why flagged: Broad catch may swallow unrelated failures and mask real bugs.
- line 52: - Why flagged: Broad catch may swallow unrelated failures and mask real bugs.
- line 55: - Why flagged: Broad catch may swallow unrelated failures and mask real bugs.
- line 58: - Why flagged: Broad catch may swallow unrelated failures and mask real bugs.
- line 61: - Why flagged: Broad catch may swallow unrelated failures and mask real bugs.
- line 66: - Code: `// Bug #14: query the real collections and fall back to mock data when`
- line 69: - Code: `// Bug #1 (2026-09-25): the healthy branch ran bare — with Mongo`
- line 72: - Code: `// Bug #3 (2026-09-25): drafts (date IS NULL) must never be`
- line 75: - Code: `// Bug #13: bound per_page; Bug #15: validate sort/direction values.`
- line 78: - Code: `// Bug #13: bound per_page.`
- line 81: - Code: `// Bug #13: bound per_page.`
- line 84: - Code: `// Bug #13: bound per_page; Bug #15: validate status values instead of`
- line 87: - Code: `// Bug #13: bound per_page.`
- line 90: - Code: `// Bug #13: bound per_page; Bug #15: validate category values.`
- line 93: - Code: `// Bug #15: unknown categories are a client error, not "no skills".`
- line 96: - Code: `* to mock data. Everything else is a real bug and must propagate.`
- line 99: - Code: `* Bug #10: the previous catch-all (\Throwable) swallowed developer errors`
- line 102: - Code: `// is I/O-free (probe bug #2), and the query itself is the probe:`
- line 105: - Code: `// Bug #12: empty Collections (all()/get() endpoints) must fall back`
- line 108: - Code: `* list endpoints serve mock content (bug #10).`
- line 111: - Code: `* Bug #11: filters were silently dropped on the fallback path.`
- line 114: - Code: `* Bug #17: keeps the query string on generated pagination links.`
- line 117: - Code: `// (Bug #16): mock JSON stores plain strings like '2024-03-15'.`
- line 120: - Code: `// Bug #2: the MongoDB query builder cannot compile selectRaw()`
- line 123: - Code: `// Bug #5: whitelist sort/direction to prevent crafted query strings`
- line 126: - Code: `// Bug #5 (2026-09-25): store() lacked the guard — with Mongo down the`
- line 129: - Code: `// Bug #8 (2026-09-25): count the models BEFORE mutating them so the`
- line 132: - Code: `// Bug #5: whitelist sort/direction to prevent crafted query strings`
- line 135: - Code: `// Bug #5 (2026-09-25): store() lacked the Mongo-down guard.`
- line 138: - Code: `// Bug #3: use the model class so the presence verifier resolves the`
- line 141: - Code: `// Bug #3: model-class rule + explicit _id key column so the`
- line 144: - Code: `// Bug #5 (2026-09-25): store() lacked the Mongo-down guard.`
- line 147: - Code: `// Bug #2 (2026-09-25): getDatabase() performs no server I/O, so the`
- line 150: - Code: `* source is read-only, so persisting anything is impossible. Bug #4.`
- line 153: - Code: `// Bug #16: normalize dates to a single ISO-8601 string whether the`
- line 156: - Code: `// share one availability probe. Bug #4: index views use these to hide`
- line 159: - Code: `* Bug #2 (2026-09-25): getMongoDB() was both deprecated and, like`
- line 162: - Code: `* Bug #2 (2026-09-25): every probe previously called getDatabase(), which only`
- line 165: - Code: `// sent to the dashboard instead of falling back to "/" (bug #7).`
- line 168: - Code: `// Bug #9 (2026-09-25): with the server unreachable, every request`
- line 171: - Code: `{{-- Bug #4 (2026-09-25): Carbon::parse(null) returns "now", so`
- line 174: - Code: `// Bug #6 (2026-09-25): rate-limit credential attempts — 5 per minute per`

## `bugs-reported/AUDIT-app-2026-09-28.md`

Status: 🟢 fixed — all 5 findings fixed + verified, plus 1 bug (F6) discovered during test verification (see `bug-fixes/AUDIT-app-2026-09-28-fixes.md`)

## `bugs-reported/AUDIT-app-2026-09-28-r2.md`

Status: 🟢 fixed — all 3 findings (F1 fallback guard, F2 probe TTL, F3 search parity) fixed + verified (see `bug-fixes/AUDIT-app-2026-09-28-r2-fixes.md`)

## `bugs-reported/AUDIT-app-2026-09-28-r3.md`

Status: 🟢 fixed — all 3 findings fixed + verified; report's F2/F3 fix proposals corrected against real code before applying (see `bug-fixes/AUDIT-app-2026-09-28-r3-fixes.md`)

- line 1: # Bug Audit — app/ folder — 2026-09-28
- line 5: **Method:** static hunt by category (security, correctness, error handling, data integrity, degraded modes, code heal...
- line 80: - `LandingController` (bug #3 comment): *"drafts (date IS NULL) must never be published on the public site"* — and th...
- line 115: **Impact:** the 2026-09-25 primary-partition fix (bug #5) added `denyWhenMongoDown()` to store/destroy/toggle/bulk in...
- line 151: - **File:** `.agents/skills/bug-finder/scripts/scan_bug_patterns.py` (outside `app/` — tooling)
- line 153: **Impact:** run from the Laravel root, it wrote to `eportfolioAPI/eportfolioAPI/bugs-reported/pattern-scan.md` (hardc...
- line 159: - **Auth flow** (`Admin\AuthController`, `routes/web.php`, `IsAdmin`): throttled login (5/min), session regenerate on...
- line 162: - **`LandingController`:** probe-gated, queries wrapped in connection-only catch + `MongoProbe::flush()` on failure, ...
- line 164: - **Fallback architecture** (`Api\V1\FallbackData`): connection-only catch (bug #10 intact), empty-collection fallbac...
- line 167: - **Bulk deletes:** Project version deletes stored images per-model and reports `deleted/attempted` counted pre-mutat...

