const BASE_URL = "http://127.0.0.1:8000/api";

export async function checkHybrid(text) {
  const res = await fetch(`${BASE_URL}/correct-text`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ text }),
  });
  return res.json();
}

export async function predictAI(text) {
  const res = await fetch(`${BASE_URL}/ai/check`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ text }),
  });
  return res.json();
}

export async function uploadPDF(file) {
  const formData = new FormData();
  formData.append("file", file);

  const res = await fetch(`${BASE_URL}/correct-pdf`, {
    method: "POST",
    body: formData,
  });

  return res.json();
}

// export async function predictMask(text) {
//   const res = await fetch(`${BASE_URL}/predict`, {
//     method: "POST",
//     headers: { "Content-Type": "application/json" },
//     body: JSON.stringify({ text }),
//   });
//   return res.json();
// }

export async function getReference(slug) {
  const res = await fetch(`${BASE_URL}/puebi/${slug}`);
  return res.json();
}