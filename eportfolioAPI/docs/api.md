# Public API Documentation

Base URL: `/api/v1`

All endpoints are **read-only** and require no authentication.

## Blog Posts

### List Blog Posts
```
GET /api/v1/blogs
```

**Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| search | string | Search title or excerpt |
| sort | string | Sort field (default: `date`) |
| direction | string | `asc` or `desc` (default: `desc`) |
| per_page | int | Results per page (default: 15) |

**Response:**
```json
{
  "data": [
    {
      "id": "...",
      "title": "Exploring Next.js 14 App Router",
      "excerpt": "A breakdown of App Router architecture...",
      "date": "2024-03-15T00:00:00.000000Z",
      "readTime": "5 min read",
      "slug": "nextjs-app-router",
      "content": "<p>...</p>"
    }
  ],
  "links": { "..." : "..." },
  "meta": { "..." : "..." }
}
```

### Get Blog Post by Slug
```
GET /api/v1/blogs/{slug}
```

---

## Experience

### List Experience
```
GET /api/v1/experiences
```

**Response:**
```json
{
  "data": [
    {
      "id": "primary",
      "position": "Full-Stack Developer",
      "yearsOfExperience": 2,
      "soloProjects": 2,
      "collabProjects": 2
    }
  ]
}
```

---

## Metrics

### List Metrics
```
GET /api/v1/metrics
```

**Response:**
```json
{
  "data": [
    {
      "id": "releases",
      "label": "Total Projects",
      "value": 5,
      "suffix": null,
      "metricDescription": "Product-style builds..."
    }
  ]
}
```

---

## Projects

### List Projects
```
GET /api/v1/projects
```

**Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| search | string | Search title or description |
| status | string | Filter by `built` or `in-progress` |
| featured | boolean | Filter by featured status |
| technology | string | Filter by technology name |
| sort | string | Sort field (default: `created_at`) |
| direction | string | `asc` or `desc` (default: `desc`) |
| per_page | int | Results per page (default: 15) |

**Response:**
```json
{
  "data": [
    {
      "id": "...",
      "title": "Property Management System",
      "description": "Multi-tenant property platform...",
      "technologies": ["React", "Tailwind CSS", "Express.js"],
      "status": "built",
      "githubLink": "https://github.com/...",
      "demoLink": "https://property-management-client.vercel.app",
      "image": "/projects/PMS.webp",
      "outcome": "Shipped with Supabase auth...",
      "metrics": ["2 role scopes", "Real-time sync"],
      "featured": true
    }
  ]
}
```

### Get Project by ID
```
GET /api/v1/projects/{id}
```

---

## Skills

### List Skills
```
GET /api/v1/skills
```

**Response:**
```json
{
  "data": [
    {
      "id": "...",
      "proficient": ["JavaScript", "TypeScript", "React"],
      "familiar": ["Next.js", "Angular", "PHP"],
      "authentication": ["Firebase Authentication", "JWT"],
      "architecture": ["Lazy Loading", "Code Splitting"],
      "toolsPlatforms": ["GitHub Actions", "Vercel"],
      "practices": ["Agile/Scrum", "CI/CD"],
      "ai": ["GitHub Copilot", "Claude"]
    }
  ]
}
```

---

## Tech Skills

### List Tech Skills
```
GET /api/v1/tech-skills
```

**Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| category | string | Filter by category (frontend, backend, etc.) |
| per_page | int | Results per page (default: 50) |

**Response:**
```json
{
  "data": [
    {
      "id": "...",
      "category": "frontend",
      "logo": "./devicons/react-original.svg",
      "label": "React"
    }
  ]
}
```

### Get Tech Skills by Category
```
GET /api/v1/tech-skills/{category}
```

**Response:**
```json
{
  "category": "frontend",
  "skills": {
    "data": [
      {
        "id": "...",
        "category": "frontend",
        "logo": "./devicons/react-original.svg",
        "label": "React"
      }
    ]
  }
}
```

---

## Error Responses

All errors return JSON:

```json
{
  "message": "Resource not found"
}
```

**Status codes:**
- `200` — Success
- `404` — Resource not found
- `500` — Server error

## Rate Limiting

No rate limiting is currently applied to the public API.
