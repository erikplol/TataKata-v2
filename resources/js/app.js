import './bootstrap';
import { checkHybrid, predictAI, uploadPDF, getReference } from './api';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// For FastAPI endpoint debug

// async function runHybridCheck() {
//   const result = await checkHybrid("Saya pergi ke sekolah");
//   console.log("checkHybrid result:", result);
// }

// async function runAIPrediction() {
//   const aiResult = await predictAI("Halo dunia");
//   console.log("predictAI result:", aiResult);
// }

// async function runMaskPrediction() {
//   const maskResult = await predictMask("Saya [MASK] ke sekolah");
//   console.log("predictMask result:", maskResult);
// }

// async function runGetReference() {
//   const ref = await getReference("penulisan-kapital");
//   console.log("getReference result:", ref);
// }
