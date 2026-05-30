import axios from 'axios';
import config from './config.js';

let token = null;
let tokenExpiry = null;

function getApi() {
  return axios.create({
    baseURL: config.medify.apiUrl,
    timeout: 10000,
    headers: token ? { Authorization: `Bearer ${token}` } : {},
  });
}

export async function login() {
  try {
    const response = await axios.post(`${config.medify.apiUrl}/token`, {
      email: config.medify.email,
      password: config.medify.password,
    });
    token = response.data.token || response.data.data?.token;
    tokenExpiry = Date.now() + 7 * 24 * 60 * 60 * 1000;
    return token;
  } catch (error) {
    throw new Error(`API login failed: ${error.message}`);
  }
}

async function ensureToken() {
  if (!token || Date.now() >= tokenExpiry - 3600000) {
    await login();
  }
}

async function request(method, path, data = null) {
  await ensureToken();
  const api = getApi();
  try {
    const response = await api({ method, url: path, data });
    return response.data;
  } catch (error) {
    if (error.response?.status === 401) {
      token = null;
      await ensureToken();
      const retryApi = getApi();
      const retryResponse = await retryApi({ method, url: path, data });
      return retryResponse.data;
    }
    throw error;
  }
}

export async function getPatientByNik(nik) {
  return request('get', `/data-pasien?nik=${nik}`);
}

export async function getPatientByRm(noRm) {
  return request('get', `/data-pasien/${noRm}`);
}

export async function createPatient(data) {
  return request('post', '/pasien-create', data);
}

export async function getClinics() {
  return request('get', '/clinics');
}

export async function getDoctors(clinicId) {
  return request('get', `/doctors?clinic_id=${clinicId}`);
}

export async function getSchedules(doctorId) {
  return request('get', `/schedules?dokter_id=${doctorId}`);
}

export async function getBedAvailability() {
  return request('get', '/ketersediaan-tempat-tidur');
}

export async function getPatientRegistrations(noRm) {
  return request('get', `/get-pendaftaran-pasien?no_rm=${noRm}`);
}

export async function createBooking(data) {
  return request('post', '/booking-create', data);
}

export async function cancelBooking(data) {
  return request('post', '/booking-cancel', data);
}

export async function editBooking(data) {
  return request('post', '/booking-edit', data);
}

export async function getQueue(params) {
  return request('get', `/antrian-pelayanan?poliklinik_id=${params.poliklinik_id}&tanggal=${params.tanggal}`);
}

export async function getMcuPackages(paketId) {
  const query = paketId ? `?paket_id=${paketId}` : '';
  return request('get', `/data-paket-mcu${query}`);
}

export async function getPatientInsurance(pasienId) {
  return request('get', `/get-list-asuransi?pasien_id=${pasienId}`);
}

export async function getDoctorLeave(tanggal, dokterId) {
  return request('get', `/get-jadwal-dokter-cuti?tanggal=${tanggal}&dokter_id=${dokterId}`);
}

export async function getServiceQuota(dokterId, poliklinikId) {
  let query = `dokter_id=${dokterId}`;
  if (poliklinikId) query += `&poliklinik_id=${poliklinikId}`;
  return request('get', `/data-kuota-layanan?${query}`);
}

export async function getSchedulesByDate(tanggal) {
  return request('get', `/get-jadwal-by-tanggal?tanggal=${tanggal}`);
}
