<x-app-layout>
    <x-slot name="header">Upload Design & File</x-slot>

    <div class="mb-8">
        <h2 style="font-family:'Bebas Neue',sans-serif;font-size:48px;letter-spacing:0.06em;color:#111;line-height:1;">Upload Design</h2>
        <p style="font-size:13px;color:#717171;margin-top:4px;">Send a file directly to a print station.</p>
    </div>

    @if (request('uploaded'))
        <div style="background:#F0FDF4;border:1.5px solid #BBF7D0;color:#15803D;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13.5px;display:flex;align-items:center;gap:8px;">
            <i class="fa-solid fa-circle-check"></i> File uploaded! Sent to Print Station.
        </div>
    @endif
    @if (session('status'))
        <div style="background:#F0FDF4;border:1.5px solid #BBF7D0;color:#15803D;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13.5px;display:flex;align-items:center;gap:8px;">
            <i class="fa-solid fa-circle-check"></i> {{ session('status') }}
        </div>
    @endif
    @if (session('error'))
        <div style="background:#FFF0F0;border:1.5px solid #FECACA;color:#B91C1C;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13.5px;display:flex;align-items:center;gap:8px;">
            <i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}
        </div>
    @endif

    <div style="background:#fff;border:1.5px solid #E5E5E5;border-radius:14px;padding:28px;max-width:520px;">
        <form method="POST" action="{{ route('uploader.store') }}" enctype="multipart/form-data" x-data="{
            rates: {{ $stations->mapWithKeys(fn ($st) => [$st->id => ($stationRates[$st->id] ?? collect())->mapWithKeys(fn ($r) => [$r->size_id => (float) $r->rate])])->toJson() }},
            cuttingRates: {{ $stations->mapWithKeys(fn ($st) => [$st->id => ($stationCuttingRates[$st->id] ?? collect())->mapWithKeys(fn ($r) => [$r->cutting_type_id => (float) $r->rate])])->toJson() }},
            laminationRates: {{ $stations->mapWithKeys(fn ($st) => [$st->id => ($stationLaminationRates[$st->id] ?? collect())->mapWithKeys(fn ($r) => [$r->lamination_type_id => (float) $r->rate])])->toJson() }},
            stationsRequireCutting: {{ $stations->mapWithKeys(fn ($st) => [$st->id => (bool) $st->requires_cutting])->toJson() }},
            stationId: '{{ $stations->firstWhere('is_default', true)?->id ?? $stations->first()?->id }}',
            sizeId: '{{ $sizes->firstWhere('is_default', true)?->id ?? $sizes->first()?->id }}',
            needsCutting: false,
            cuttingTypeId: '{{ $cuttingTypes->firstWhere('is_default', true)?->id ?? $cuttingTypes->first()?->id }}',
            needsLamination: null,
            laminationTypeId: '{{ $laminationTypes->firstWhere('is_default', true)?->id ?? $laminationTypes->first()?->id }}',
            sheetsCount: 1,
            labels: [{ name: '', pcs: 1 }],
            addLabelRow() { this.labels.push({ name: '', pcs: 1 }) },
            removeLabelRow(i) { if (this.labels.length > 1) this.labels.splice(i, 1) },
            uploading: false,
            uploadPct: 0,
            uploadDone: false,
            uploadError: '',
            async submitForm(e) {
                if (this.needsLamination === null) return;
                const form = e.target.closest('form');
                const fileInput = form.querySelector('[name=design_file]');
                const file = fileInput.files[0];
                if (!file) return;

                this.uploading = true;
                this.uploadPct = 0;
                this.uploadDone = false;
                this.uploadError = '';

                const CHUNK_SIZE = 512 * 1024;
                const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
                const uploadId = ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c => (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16));
                const csrfToken = form.querySelector('[name=_token]').value;
                const chunkUrl = '{{ route('uploader.chunk') }}';

                const formData = new FormData(form);
                const fields = {};
                for (const [k, v] of formData.entries()) {
                    if (k !== 'design_file' && k !== '_token' && k !== '_method') fields[k] = v;
                }

                for (let i = 0; i < totalChunks; i++) {
                    const start = i * CHUNK_SIZE;
                    const chunk = file.slice(start, start + CHUNK_SIZE);

                    const fd = new FormData();
                    fd.append('_token', csrfToken);
                    fd.append('upload_id', uploadId);
                    fd.append('chunk_index', i);
                    fd.append('total_chunks', totalChunks);
                    fd.append('chunk', chunk, file.name);
                    fd.append('original_name', file.name);

                    if (i === totalChunks - 1) {
                        for (const [k, v] of Object.entries(fields)) fd.append(k, v);
                        this.labels.forEach((row, li) => {
                            fd.append(`labels[${li}][name]`, row.name);
                            fd.append(`labels[${li}][pcs]`, row.pcs);
                        });
                    }

                    const sendChunk = (attempt) => new Promise((resolve, reject) => {
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', chunkUrl);
                        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                        xhr.addEventListener('load', () => {
                            if (xhr.status >= 200 && xhr.status < 300) {
                                try { resolve(JSON.parse(xhr.responseText)); }
                                catch (e) { reject(new Error('Invalid server response')); }
                            } else if ((xhr.status === 502 || xhr.status === 504) && attempt < 3) {
                                setTimeout(() => sendChunk(attempt + 1).then(resolve).catch(reject), 1500 * attempt);
                            } else {
                                const tmp = document.createElement('div');
                                tmp.innerHTML = xhr.responseText;
                                const text = tmp.textContent.trim().replace(/\s+/g, ' ').substring(0, 120);
                                reject(new Error(text || 'HTTP ' + xhr.status));
                            }
                        });
                        xhr.addEventListener('error', () => reject(new Error('Network error')));
                        xhr.send(fd);
                    });

                    try {
                        const res = await sendChunk(1);
                        this.uploadPct = Math.round(((i + 1) / totalChunks) * 100);
                        if (res.status === 'done') {
                            this.uploadDone = true;
                            setTimeout(() => { window.location = '{{ route('uploader.create') }}?uploaded=1'; }, 700);
                            return;
                        }
                    } catch (err) {
                        this.uploading = false;
                        this.uploadError = 'Upload failed (chunk ' + (i+1) + '/' + totalChunks + '). ' + (err.message || 'Please try again.');
                        return;
                    }
                }
            },
        }" @submit.prevent="submitForm($event)">
            @csrf

            {{-- File input --}}
            <div style="margin-bottom:18px;">
                <label style="display:block;font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#777;margin-bottom:8px;">
                    <i class="fa-solid fa-paperclip"></i> Design File <span style="color:#EF4444;">*</span>
                </label>
                <input type="file" name="design_file" required
                    style="width:100%;font-size:13px;color:#555;border:2px dashed #D5D5D5;border-radius:10px;background:#FAFAF8;padding:12px 14px;cursor:pointer;outline:none;">
            </div>

            {{-- Note --}}
            <div style="margin-bottom:18px;">
                <label style="display:block;font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#777;margin-bottom:8px;">Note (Optional)</label>
                <input type="text" name="note" placeholder="e.g., Urgent Print, Customer Name..."
                    style="width:100%;border:1.5px solid #E5E5E5;border-radius:10px;padding:11px 14px;font-size:13.5px;font-family:'DM Sans',sans-serif;color:#111;outline:none;background:#FAFAF8;">
            </div>

            {{-- Print Station --}}
            <div style="margin-bottom:18px;">
                <label style="display:block;font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#777;margin-bottom:8px;">Print Station <span style="color:#EF4444;">*</span></label>
                <select name="print_station_id" x-model="stationId" required
                    style="width:100%;border:1.5px solid #E5E5E5;border-radius:10px;padding:11px 14px;font-size:13.5px;font-family:'DM Sans',sans-serif;color:#111;outline:none;background:#FAFAF8;">
                    @foreach ($stations as $station)
                        <option value="{{ $station->id }}" @selected($station->is_default)>{{ $station->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Size --}}
            <div style="margin-bottom:18px;">
                <label style="display:block;font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#777;margin-bottom:8px;">Size</label>
                <select name="size_id" x-model="sizeId"
                    style="width:100%;border:1.5px solid #E5E5E5;border-radius:10px;padding:11px 14px;font-size:13.5px;font-family:'DM Sans',sans-serif;color:#111;outline:none;background:#FAFAF8;">
                    @foreach ($sizes as $size)
                        <option value="{{ $size->id }}" @selected($size->is_default)>{{ $size->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Sheets --}}
            <div style="margin-bottom:18px;">
                <label style="display:block;font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#777;margin-bottom:8px;">Copies / Sheets <span style="color:#EF4444;">*</span></label>
                <input type="number" name="sheets" min="1" value="1" required x-model.number="sheetsCount"
                    style="width:100%;border:1.5px solid #E5E5E5;border-radius:10px;padding:11px 14px;font-size:13.5px;font-family:'DM Sans',sans-serif;color:#111;outline:none;background:#FAFAF8;">
            </div>

            {{-- Rate badge --}}
            <div style="background:#FFF7ED;border:1.5px solid #FED7AA;border-radius:10px;padding:12px 16px;margin-bottom:18px;font-size:13px;font-weight:700;color:#C2410C;display:flex;align-items:center;gap:8px;">
                <i class="fa-solid fa-tags"></i>
                Size Rate: <span x-text="rates[stationId]?.[sizeId]"></span> Rs / sheet
            </div>

            {{-- Cutting --}}
            <div style="margin-bottom:18px;" x-show="stationsRequireCutting[stationId]">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:#333;margin-bottom:10px;cursor:pointer;">
                    <input type="checkbox" name="needs_cutting" value="1" x-model="needsCutting" style="accent-color:#F05A28;width:15px;height:15px;">
                    Needs Cutting?
                </label>
                <div x-show="needsCutting">
                    <label style="display:block;font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#777;margin-bottom:8px;">Cutting Type</label>
                    <select name="cutting_type_id" x-model="cuttingTypeId"
                        style="width:100%;border:1.5px solid #E5E5E5;border-radius:10px;padding:11px 14px;font-size:13.5px;font-family:'DM Sans',sans-serif;color:#111;outline:none;background:#FAFAF8;">
                        @foreach ($cuttingTypes as $type)
                            <option value="{{ $type->id }}" @selected($type->is_default)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                    <div style="background:#FAF5FF;border:1.5px solid #E9D5FF;border-radius:10px;padding:12px 16px;margin-top:10px;font-size:13px;font-weight:700;color:#7E22CE;display:flex;align-items:center;gap:8px;">
                        <i class="fa-solid fa-scissors"></i>
                        Cutting Rate: <span x-text="cuttingRates[stationId]?.[cuttingTypeId]"></span> Rs / cut
                    </div>
                </div>
            </div>

            {{-- Lamination --}}
            <div style="margin-bottom:18px;">
                <label style="display:block;font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#777;margin-bottom:10px;">
                    <i class="fa-solid fa-layer-group"></i> Lamination Required? <span style="color:#EF4444;">*</span>
                </label>
                <div style="display:flex;gap:10px;">
                    <button type="button" @click="needsLamination = false"
                        :style="needsLamination === false ? 'background:#111;color:#fff;border-color:#111;' : 'background:#fff;color:#555;border-color:#E5E5E5;'"
                        style="flex:1;border:2px solid;border-radius:10px;padding:11px;font-size:13px;font-weight:700;font-family:\'DM Sans\',sans-serif;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;transition:all 0.15s;">
                        <i class="fa-solid fa-xmark"></i> No Lamination
                    </button>
                    <button type="button" @click="needsLamination = true"
                        :style="needsLamination === true ? 'background:#F05A28;color:#fff;border-color:#F05A28;' : 'background:#fff;color:#555;border-color:#E5E5E5;'"
                        style="flex:1;border:2px solid;border-radius:10px;padding:11px;font-size:13px;font-weight:700;font-family:\'DM Sans\',sans-serif;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;transition:all 0.15s;">
                        <i class="fa-solid fa-layer-group"></i> Yes, Laminate
                    </button>
                </div>
                <input type="hidden" name="needs_lamination" :value="needsLamination === true ? '1' : '0'">

                <div x-show="needsLamination === true" x-transition style="margin-top:14px;">
                    <label style="display:block;font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#777;margin-bottom:8px;">Lamination Type</label>
                    <select name="lamination_type_id" x-model="laminationTypeId"
                        style="width:100%;border:1.5px solid #E5E5E5;border-radius:10px;padding:11px 14px;font-size:13.5px;font-family:'DM Sans',sans-serif;color:#111;outline:none;background:#FAFAF8;">
                        @foreach ($laminationTypes as $type)
                            <option value="{{ $type->id }}" @selected($type->is_default)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                    <div style="background:#FFF7ED;border:1.5px solid #FED7AA;border-radius:10px;padding:12px 16px;margin-top:10px;font-size:13px;font-weight:700;color:#C2410C;display:flex;align-items:center;gap:8px;">
                        <i class="fa-solid fa-layer-group"></i>
                        Lamination Rate: <span x-text="laminationRates[stationId]?.[laminationTypeId] ?? 0"></span> Rs / sheet
                    </div>
                </div>
            </div>

            {{-- Sheet contents / labels --}}
            <div style="margin-bottom:18px;">
                <label style="display:block;font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#777;margin-bottom:6px;">
                    <i class="fa-solid fa-tags"></i> Sheet Contents (Optional)
                </label>
                <p style="font-size:12px;color:#A0A0A0;margin-bottom:12px;">Sheet ma ketla label che ane ketla pcs.</p>

                <datalist id="label-suggestions">
                    @foreach ($labelSuggestions as $s)
                        <option value="{{ $s }}">
                    @endforeach
                </datalist>

                <template x-for="(row, i) in labels" :key="i">
                    <div style="display:flex;gap:8px;margin-bottom:8px;align-items:center;">
                        <input type="text" :name="`labels[${i}][name]`" x-model="row.name"
                            list="label-suggestions"
                            placeholder="Label name (e.g. Ultra Gold 1ltr)"
                            style="flex:1;border:1.5px solid #E5E5E5;border-radius:9px;padding:9px 12px;font-size:13px;font-family:'DM Sans',sans-serif;color:#111;outline:none;background:#FAFAF8;">
                        <input type="number" :name="`labels[${i}][pcs]`" x-model.number="row.pcs" min="1"
                            placeholder="Pcs"
                            style="width:70px;border:1.5px solid #E5E5E5;border-radius:9px;padding:9px 8px;font-size:13px;text-align:center;font-family:'DM Sans',sans-serif;color:#111;outline:none;background:#FAFAF8;">
                        <span style="font-size:12px;color:#A0A0A0;font-weight:600;white-space:nowrap;">
                            × <span x-text="sheetsCount"></span> = <span style="color:#F05A28;font-weight:700;" x-text="(parseInt(row.pcs) || 0) * sheetsCount"></span>
                        </span>
                        <button type="button" @click="removeLabelRow(i)" x-show="labels.length > 1"
                            style="background:none;border:none;color:#EF4444;cursor:pointer;padding:6px;border-radius:6px;font-size:14px;">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </template>

                <button type="button" @click="addLabelRow()"
                    style="background:none;border:none;color:#F05A28;font-size:13px;font-weight:700;font-family:'DM Sans',sans-serif;cursor:pointer;display:flex;align-items:center;gap:6px;padding:0;margin-top:4px;">
                    <i class="fa-solid fa-plus"></i> Add another label
                </button>
            </div>

            {{-- Upload error --}}
            <div x-show="uploadError" style="background:#FFF0F0;border:1.5px solid #FECACA;border-radius:10px;padding:12px 16px;margin-bottom:14px;display:flex;align-items:flex-start;gap:10px;">
                <i class="fa-solid fa-circle-xmark" style="color:#EF4444;margin-top:2px;"></i>
                <div>
                    <p style="color:#B91C1C;font-size:13px;font-weight:600;" x-text="uploadError"></p>
                    <button type="button" @click="uploadError=''; uploading=false;" style="background:none;border:none;font-size:12px;color:#EF4444;text-decoration:underline;cursor:pointer;padding:0;margin-top:4px;">Try again</button>
                </div>
            </div>

            {{-- Upload progress --}}
            <div x-show="uploading && !uploadError" style="margin-bottom:14px;">
                <div x-show="!uploadDone" style="display:flex;flex-direction:column;align-items:center;padding:20px 0;">
                    <style>
                        @keyframes dance {
                            0%   { transform: rotate(-15deg) translateY(0px) scale(1); }
                            15%  { transform: rotate(15deg)  translateY(-8px) scale(1.1); }
                            30%  { transform: rotate(-10deg) translateY(0px) scale(1); }
                            45%  { transform: rotate(10deg)  translateY(-5px) scale(1.05); }
                            60%  { transform: rotate(-15deg) translateY(0px) scale(1); }
                            75%  { transform: rotate(15deg)  translateY(-8px) scale(1.1); }
                            90%  { transform: rotate(-8deg)  translateY(0px) scale(1); }
                            100% { transform: rotate(-15deg) translateY(0px) scale(1); }
                        }
                        @keyframes shadow-pulse {
                            0%, 100% { transform: scaleX(1); opacity: 0.3; }
                            40%       { transform: scaleX(0.6); opacity: 0.15; }
                        }
                        .dancer { animation: dance 0.7s ease-in-out infinite; display: inline-block; font-size: 3.5rem; line-height: 1; }
                        .dancer-shadow { width: 40px; height: 8px; background: radial-gradient(ellipse, #94a3b8 0%, transparent 70%); margin: 2px auto 0; animation: shadow-pulse 0.7s ease-in-out infinite; }
                    </style>
                    <span class="dancer">🕺</span>
                    <div class="dancer-shadow"></div>
                    <p style="font-size:12px;color:#A0A0A0;margin-top:12px;font-weight:500;">Uploading your file... hang tight!</p>
                </div>
                <div x-show="uploadDone" style="display:flex;flex-direction:column;align-items:center;padding:12px 0;">
                    <span style="font-size:3rem;">🎉</span>
                    <p style="font-size:12px;color:#15803D;font-weight:700;margin-top:4px;">Upload complete!</p>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:600;color:#777;margin-bottom:6px;">
                    <span x-text="uploadPct + '% uploaded'"></span>
                </div>
                <div style="width:100%;background:#F0F0EE;border-radius:999px;height:10px;overflow:hidden;">
                    <div style="height:10px;border-radius:999px;transition:width 0.2s;"
                        :style="`width: ${uploadPct}%; background: ${uploadDone ? '#22C55E' : '#F05A28'}`"></div>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit"
                :disabled="needsLamination === null || uploading"
                :style="uploading ? 'background:#D0D0D0;cursor:not-allowed;' : needsLamination !== null ? 'background:#111;cursor:pointer;' : 'background:#D0D0D0;cursor:not-allowed;'"
                style="width:100%;color:#fff;font-family:'DM Sans',sans-serif;font-size:14px;font-weight:700;border:none;border-radius:10px;padding:14px;display:flex;align-items:center;justify-content:center;gap:8px;transition:background 0.15s;"
                onmouseover="if(this.style.background!='rgb(208, 208, 208)') this.style.background='#F05A28'"
                onmouseout="if(this.style.background!='rgb(208, 208, 208)') this.style.background='#111'">
                <span x-show="!uploading" x-text="needsLamination === null ? 'Select lamination option above' : 'Upload & Send to Print'"></span>
                <i class="fa-solid fa-paper-plane" x-show="needsLamination !== null && !uploading"></i>
                <span x-show="uploading" style="display:flex;align-items:center;gap:8px;">
                    <svg style="animation:spin 1s linear infinite;width:16px;height:16px;" fill="none" viewBox="0 0 24 24">
                        <circle style="opacity:0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path style="opacity:0.75;" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span x-text="uploadPct + '% uploaded'"></span>
                </span>
            </button>
        </form>
    </div>

    {{-- Today's label summary --}}
    @if ($dailySummary->isNotEmpty())
        <div style="margin-top:32px;max-width:520px;background:#fff;border:1.5px solid #E5E5E5;border-radius:14px;padding:22px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
                <div style="width:32px;height:32px;background:#111;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid fa-chart-bar" style="color:#F05A28;font-size:13px;"></i>
                </div>
                <span style="font-family:'Bebas Neue',sans-serif;font-size:20px;letter-spacing:0.06em;color:#111;">Today's Label Summary</span>
                <span style="font-size:12px;color:#A0A0A0;">({{ today()->format('d/m/Y') }})</span>
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                @foreach ($dailySummary as $row)
                    <div style="display:flex;align-items:center;justify-content:space-between;background:#FAFAF8;border:1px solid #E5E5E5;border-radius:9px;padding:10px 14px;">
                        <span style="font-size:13px;color:#333;font-weight:500;">{{ $row->label_name }}</span>
                        <span style="font-size:13px;font-weight:700;color:#F05A28;">{{ number_format($row->total_pcs) }} pcs</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Jobs tabs --}}
    @php
        $statusConfig = [
            'pending'   => ['label' => 'Pending',   'bg' => '#FFF7ED', 'color' => '#C2410C'],
            'cutting'   => ['label' => 'Cutting',   'bg' => '#FAF5FF', 'color' => '#7E22CE'],
            'dispatch'  => ['label' => 'Dispatch',  'bg' => '#F0F9FF', 'color' => '#0369A1'],
            'completed' => ['label' => 'Completed', 'bg' => '#F0FDF4', 'color' => '#15803D'],
        ];
    @endphp
    <div style="margin-top:32px;max-width:520px;" x-data="{ tab: 'my' }">
        {{-- Tab buttons --}}
        <div style="display:flex;gap:6px;margin-bottom:16px;background:#F5F5F3;border-radius:12px;padding:5px;width:fit-content;">
            <button type="button" @click="tab = 'my'"
                :style="tab === 'my' ? 'background:#111;color:#fff;' : 'background:transparent;color:#717171;'"
                style="padding:8px 18px;border-radius:9px;border:none;font-size:13px;font-weight:700;font-family:\'DM Sans\',sans-serif;cursor:pointer;display:flex;align-items:center;gap:7px;transition:all 0.15s;">
                <i class="fa-solid fa-user"></i> My Jobs
                <span style="background:rgba(255,255,255,0.2);border-radius:999px;padding:1px 7px;font-size:11px;">{{ $myJobs->count() }}</span>
            </button>
            <button type="button" @click="tab = 'all'"
                :style="tab === 'all' ? 'background:#111;color:#fff;' : 'background:transparent;color:#717171;'"
                style="padding:8px 18px;border-radius:9px;border:none;font-size:13px;font-weight:700;font-family:\'DM Sans\',sans-serif;cursor:pointer;display:flex;align-items:center;gap:7px;transition:all 0.15s;">
                <i class="fa-solid fa-list"></i> All Jobs
                <span style="background:rgba(255,255,255,0.2);border-radius:999px;padding:1px 7px;font-size:11px;">{{ $allJobs->count() }}</span>
            </button>
        </div>

        {{-- My Jobs --}}
        <div x-show="tab === 'my'">
            @if ($myJobs->isEmpty())
                <p style="color:#A0A0A0;font-size:13.5px;text-align:center;padding:32px 0;">You have no jobs yet.</p>
            @else
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @foreach ($myJobs as $job)
                        @php $st = $statusConfig[$job->status->value] ?? ['label' => $job->status->value, 'bg' => '#F5F5F3', 'color' => '#717171']; @endphp
                        <div style="background:#fff;border:1.5px solid #E5E5E5;border-radius:12px;padding:14px 16px;" x-data="{ editNote: false }">
                            <div style="display:flex;align-items:flex-start;gap:12px;">
                                @php $isImage = str_starts_with($job->mime_type ?? '', 'image/'); @endphp
                                @if ($job->fileUrl() && str_contains($job->mime_type ?? '', 'pdf'))
                                    <div style="width:120px;height:120px;flex-shrink:0;border-radius:10px;background:#FFF0F0;border:1.5px solid #FECACA;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;color:#EF4444;">
                                        <i class="fa-solid fa-file-pdf" style="font-size:36px;"></i>
                                        <span style="font-size:10px;font-weight:700;letter-spacing:0.05em;">PDF</span>
                                    </div>
                                @elseif ($job->fileUrl() && $isImage)
                                    <img src="{{ $job->fileUrl() }}" alt="{{ $job->file_name }}"
                                        style="width:120px;height:120px;flex-shrink:0;border-radius:10px;object-fit:cover;border:1.5px solid #E5E5E5;">
                                @elseif ($job->fileUrl())
                                    <div style="width:120px;height:120px;flex-shrink:0;border-radius:10px;background:#F5F5F3;border:1.5px solid #E5E5E5;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;color:#999;">
                                        <i class="fa-solid fa-file" style="font-size:36px;"></i>
                                        <span style="font-size:9px;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;">{{ strtoupper(pathinfo($job->file_name, PATHINFO_EXTENSION)) }}</span>
                                    </div>
                                @else
                                    <div style="width:120px;height:120px;flex-shrink:0;border-radius:10px;background:#F5F5F3;border:1.5px solid #E5E5E5;display:flex;align-items:center;justify-content:center;color:#CCC;">
                                        <i class="fa-solid fa-image" style="font-size:32px;"></i>
                                    </div>
                                @endif
                                <div style="flex:1;min-width:0;">
                                    <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:4px;">
                                        <span style="font-weight:700;font-size:13.5px;color:#111;">#{{ $job->id }}</span>
                                        <span style="background:{{ $st['bg'] }};color:{{ $st['color'] }};font-size:10.5px;font-weight:700;padding:2px 8px;border-radius:5px;text-transform:uppercase;letter-spacing:0.06em;">{{ $st['label'] }}</span>
                                        <span style="background:#F5F5F3;color:#555;font-size:10.5px;font-weight:600;padding:2px 8px;border-radius:5px;border:1px solid #E5E5E5;">{{ $job->printStation?->name ?? '—' }}</span>
                                        <span style="background:#F5F5F3;color:#777;font-size:10.5px;padding:2px 8px;border-radius:5px;border:1px solid #E5E5E5;">{{ $job->size?->name ?? '—' }}</span>
                                    </div>
                                    <div x-show="!editNote" style="display:flex;align-items:center;gap:8px;">
                                        <span style="font-size:13px;color:#555;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $job->note }}</span>
                                        <button type="button" @click="editNote = true"
                                            style="background:none;border:none;font-size:12px;color:#F05A28;cursor:pointer;flex-shrink:0;font-weight:600;">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit Note
                                        </button>
                                    </div>
                                    <form x-show="editNote" method="POST" action="{{ route('jobs.note.update', $job) }}" style="display:flex;gap:6px;margin-top:4px;">
                                        @csrf @method('PATCH')
                                        <input type="text" name="note" value="{{ $job->note === '-' ? '' : $job->note }}"
                                            placeholder="Enter note..."
                                            style="flex:1;border:1.5px solid #E5E5E5;border-radius:8px;padding:6px 10px;font-size:12.5px;min-width:0;font-family:'DM Sans',sans-serif;outline:none;">
                                        <button type="submit" style="background:#F05A28;color:#fff;border:none;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">Save</button>
                                        <button type="button" @click="editNote = false" style="background:none;border:none;font-size:12px;color:#A0A0A0;cursor:pointer;">Cancel</button>
                                    </form>
                                    @if ($job->jobLabels->isNotEmpty())
                                        <div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:4px;">
                                            @foreach ($job->jobLabels as $lbl)
                                                <span style="background:#FFF7ED;border:1px solid #FED7AA;color:#C2410C;font-size:11px;padding:2px 8px;border-radius:999px;display:inline-flex;align-items:center;gap:4px;">
                                                    {{ $lbl->label_name }}
                                                    <strong>{{ $lbl->pcs_per_sheet }} × {{ $job->sheets }} = {{ $lbl->pcs_per_sheet * $job->sheets }} pcs</strong>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                    <div style="font-size:11.5px;color:#A0A0A0;margin-top:4px;">{{ $job->created_at->format('d/m/Y h:i A') }}</div>
                                </div>
                                @if ($job->status->value === 'pending')
                                    <form method="POST" action="{{ route('jobs.destroy', $job) }}"
                                        onsubmit="return confirm('Delete Job #{{ $job->id }}? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" style="background:none;border:none;color:#EF4444;cursor:pointer;padding:8px;border-radius:8px;font-size:14px;flex-shrink:0;" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- All Jobs --}}
        <div x-show="tab === 'all'">
            @if ($allJobs->isEmpty())
                <p style="color:#A0A0A0;font-size:13.5px;text-align:center;padding:32px 0;">No jobs found.</p>
            @else
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @foreach ($allJobs as $job)
                        @php $st = $statusConfig[$job->status->value] ?? ['label' => $job->status->value, 'bg' => '#F5F5F3', 'color' => '#717171']; @endphp
                        <div style="background:#fff;border:1.5px solid #E5E5E5;border-radius:12px;padding:14px 16px;">
                            <div style="display:flex;align-items:flex-start;gap:12px;">
                                @php $isImage2 = str_starts_with($job->mime_type ?? '', 'image/'); @endphp
                                @if ($job->fileUrl() && str_contains($job->mime_type ?? '', 'pdf'))
                                    <div style="width:120px;height:120px;flex-shrink:0;border-radius:10px;background:#FFF0F0;border:1.5px solid #FECACA;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;color:#EF4444;">
                                        <i class="fa-solid fa-file-pdf" style="font-size:36px;"></i>
                                        <span style="font-size:10px;font-weight:700;">PDF</span>
                                    </div>
                                @elseif ($job->fileUrl() && $isImage2)
                                    <img src="{{ $job->fileUrl() }}" alt="{{ $job->file_name }}"
                                        style="width:120px;height:120px;flex-shrink:0;border-radius:10px;object-fit:cover;border:1.5px solid #E5E5E5;">
                                @elseif ($job->fileUrl())
                                    <div style="width:120px;height:120px;flex-shrink:0;border-radius:10px;background:#F5F5F3;border:1.5px solid #E5E5E5;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;color:#999;">
                                        <i class="fa-solid fa-file" style="font-size:36px;"></i>
                                        <span style="font-size:9px;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;">{{ strtoupper(pathinfo($job->file_name, PATHINFO_EXTENSION)) }}</span>
                                    </div>
                                @else
                                    <div style="width:120px;height:120px;flex-shrink:0;border-radius:10px;background:#F5F5F3;border:1.5px solid #E5E5E5;display:flex;align-items:center;justify-content:center;color:#CCC;">
                                        <i class="fa-solid fa-image" style="font-size:32px;"></i>
                                    </div>
                                @endif
                                <div style="flex:1;min-width:0;">
                                    <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:4px;">
                                        <span style="font-weight:700;font-size:13.5px;color:#111;">#{{ $job->id }}</span>
                                        <span style="background:{{ $st['bg'] }};color:{{ $st['color'] }};font-size:10.5px;font-weight:700;padding:2px 8px;border-radius:5px;text-transform:uppercase;letter-spacing:0.06em;">{{ $st['label'] }}</span>
                                        <span style="background:#F5F5F3;color:#555;font-size:10.5px;font-weight:600;padding:2px 8px;border-radius:5px;border:1px solid #E5E5E5;">{{ $job->printStation?->name ?? '—' }}</span>
                                        <span style="background:#F5F5F3;color:#777;font-size:10.5px;padding:2px 8px;border-radius:5px;border:1px solid #E5E5E5;">{{ $job->size?->name ?? '—' }}</span>
                                    </div>
                                    <div style="font-size:13px;color:#555;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $job->note }}</div>
                                    @if ($job->jobLabels->isNotEmpty())
                                        <div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:4px;">
                                            @foreach ($job->jobLabels as $lbl)
                                                <span style="background:#FFF7ED;border:1px solid #FED7AA;color:#C2410C;font-size:11px;padding:2px 8px;border-radius:999px;display:inline-flex;align-items:center;gap:4px;">
                                                    {{ $lbl->label_name }}
                                                    <strong>{{ $lbl->pcs_per_sheet }} × {{ $job->sheets }} = {{ $lbl->pcs_per_sheet * $job->sheets }} pcs</strong>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                    <div style="font-size:11.5px;color:#A0A0A0;margin-top:4px;">
                                        {{ $job->uploader?->name ?? '—' }} · {{ $job->created_at->format('d/m/Y h:i A') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
