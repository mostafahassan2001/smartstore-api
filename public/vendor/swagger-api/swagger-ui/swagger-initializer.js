window.onload = function () {
  let originalUrl = "http://proud-celebration-production-82f8.up.railway.app/storage/api-docs/api-docs.json";
  let secureUrl = originalUrl.startsWith("http://")
    ? originalUrl.replace("http://", "https://")
    : originalUrl;

  window.ui = SwaggerUIBundle({
    url: secureUrl,
    dom_id: '#swagger-ui',
    deepLinking: true,
    presets: [
      SwaggerUIBundle.presets.apis,
      SwaggerUIStandalonePreset
    ],
    plugins: [
      SwaggerUIBundle.plugins.DownloadUrl
    ],
    layout: "StandaloneLayout"
  });
};
