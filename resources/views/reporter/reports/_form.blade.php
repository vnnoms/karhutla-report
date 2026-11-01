<form class="report-form" method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if ($report->exists)
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="form-alert" role="alert">
            <p>Periksa kembali data laporan Anda.</p>
            <ul>
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="form-section">
        <div class="form-section-heading">
            <span class="form-step">01</span>
            <div>
                <h2>Informasi kejadian</h2>
                <p>Ceritakan kondisi yang Anda lihat di lokasi.</p>
            </div>
        </div>
        <div class="field">
            <label for="report_description">Deskripsi laporan</label>
            <textarea id="report_description" name="report_description" rows="5" maxlength="10000" required placeholder="Jelaskan apa yang terjadi, kondisi sekitar, dan informasi penting lainnya.">{{ old('report_description', $report->report_description) }}</textarea>
            @error('report_description')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-grid">
            <div class="field">
                <label for="incident_time">Waktu kejadian</label>
                <input id="incident_time" name="incident_time" type="datetime-local" value="{{ old('incident_time', $report->exists ? $report->incident_time?->format('Y-m-d\TH:i') : '') }}" required>
                @error('incident_time')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>
            <div class="field">
                <label for="condition_level">Tingkat kondisi kebakaran</label>
                <select id="condition_level" name="condition_level" required>
                    <option value="">Pilih tingkat kondisi</option>
                    <option value="low" @selected(old('condition_level', $report->condition_level) === 'low')>Rendah</option>
                    <option value="medium" @selected(old('condition_level', $report->condition_level) === 'medium')>Sedang</option>
                    <option value="high" @selected(old('condition_level', $report->condition_level) === 'high')>Tinggi</option>
                    <option value="critical" @selected(old('condition_level', $report->condition_level) === 'critical')>Kritis</option>
                </select>
                @error('condition_level')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </section>

    <section class="form-section">
        <div class="form-section-heading">
            <span class="form-step">02</span>
            <div>
                <h2>Lokasi kejadian</h2>
                <p>Masukkan alamat dan koordinat jika tersedia.</p>
            </div>
        </div>
        <div class="field">
            <label for="detail_address">Detail alamat</label>
            <textarea id="detail_address" name="detail_address" rows="3" maxlength="10000" required placeholder="Nama desa, kecamatan, patokan lokasi, dan detail lainnya.">{{ old('detail_address', $report->address?->detail_address) }}</textarea>
            @error('detail_address')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-grid form-grid-three">
            <div class="field">
                <label for="city_regency">Kota/Kabupaten</label>
                <input id="city_regency" name="city_regency" type="text" value="{{ old('city_regency', $report->address?->city_regency) }}" maxlength="100" required>
                @error('city_regency')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>
            <div class="field">
                <label for="province">Provinsi</label>
                <input id="province" name="province" type="text" value="{{ old('province', $report->address?->province) }}" maxlength="100" required>
                @error('province')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>
            <div class="field">
                <label for="island">Pulau</label>
                <input id="island" name="island" type="text" value="{{ old('island', $report->address?->island) }}" maxlength="100" required>
                @error('island')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="form-grid">
            <div class="field">
                <label for="latitude">Latitude <span class="field-hint">opsional</span></label>
                <input id="latitude" name="latitude" type="number" step="any" min="-90" max="90" value="{{ old('latitude', $report->latitude) }}" placeholder="Contoh: -0.7893">
                @error('latitude')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>
            <div class="field">
                <label for="longitude">Longitude <span class="field-hint">opsional</span></label>
                <input id="longitude" name="longitude" type="number" step="any" min="-180" max="180" value="{{ old('longitude', $report->longitude) }}" placeholder="Contoh: 113.9213">
                @error('longitude')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </section>

    <section class="form-section">
        <div class="form-section-heading">
            <span class="form-step">03</span>
            <div>
                <h2>Foto bukti</h2>
                <p>Unggah foto lokasi atau kondisi kebakaran jika tersedia.</p>
            </div>
        </div>
        @if ($report->evidence_file)
            <p class="existing-evidence">Foto bukti tersimpan. Unggah foto baru untuk menggantinya.</p>
        @endif
        <div class="field">
            <label for="evidence_file">Pilih foto <span class="field-hint">JPG, PNG, atau WebP · maks. 5 MB</span></label>
            <input class="file-input" id="evidence_file" name="evidence_file" type="file" accept="image/jpeg,image/png,image/webp">
            @error('evidence_file')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>
    </section>

    <div class="form-actions">
        <a class="button button-secondary" href="{{ $report->exists ? route('reporter.reports.show', $report) : route('reporter.reports.index') }}">Batal</a>
        <button class="button" type="submit">{{ $submitLabel }} <span aria-hidden="true">→</span></button>
    </div>
</form>
