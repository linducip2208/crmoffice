<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        {{-- Provider Presets --}}
        <div class="mt-6">
            <div class="fi-section">
                <div class="fi-section-header">
                    <h3 class="fi-section-header-heading" style="font-size:16px;font-weight:700">Provider Presets</h3>
                    <p class="fi-section-header-description">Klik <strong>Gunakan Provider</strong> untuk menambah provider, lalu isi API key di halaman Providers dan aktifkan.</p>
                </div>
                <div class="fi-section-content-ctn">
                    <div class="fi-section-content">
                        @php
                            $tierColors = [
                                'free' => ['bg' => '#f3f4f6', 'text' => '#374151', 'label' => 'Gratis'],
                                'budget' => ['bg' => '#dcfce7', 'text' => '#15803d', 'label' => 'Budget'],
                                'value' => ['bg' => '#dbeafe', 'text' => '#1d4ed8', 'label' => 'Value'],
                                'mid' => ['bg' => '#fef3c7', 'text' => '#92400e', 'label' => 'Mid'],
                                'premium' => ['bg' => '#f3e8ff', 'text' => '#7c3aed', 'label' => 'Premium'],
                            ];
                        @endphp

                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:16px">
                            @foreach ($this->providerPresets as $preset)
                                @php
                                    $tier = $tierColors[$preset['tier']] ?? $tierColors['free'];
                                    $nameLower = \Illuminate\Support\Str::lower($preset['name']);
                                    $alreadyExists = in_array($nameLower, $this->existingProviderNames);
                                @endphp
                                <div style="border:1px solid #e5e7eb;border-radius:12px;padding:18px;display:flex;flex-direction:column;transition:box-shadow .2s,transform .2s"
                                     onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,.08)';this.style.transform='translateY(-2px)'"
                                     onmouseout="this.style.boxShadow='none';this.style.transform='translateY(0)'">
                                    {{-- Header: emoji + name + tier badge --}}
                                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                                        <div style="display:flex;align-items:center;gap:8px">
                                            <span style="font-size:24px">{{ $preset['emoji'] ?? '🤖' }}</span>
                                            <strong style="font-size:15px;color:#1e293b">{{ $preset['name'] }}</strong>
                                        </div>
                                        <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:9999px;font-size:11px;font-weight:600;background:{{ $tier['bg'] }};color:{{ $tier['text'] }}">
                                            {{ $tier['label'] }}
                                        </span>
                                    </div>

                                    {{-- Description --}}
                                    <p style="font-size:13px;color:#64748b;margin-bottom:8px;line-height:1.5;flex-grow:1">
                                        {{ $preset['description'] }}
                                    </p>

                                    {{-- Pricing --}}
                                    <div style="font-size:12px;color:#6b7280;margin-bottom:10px">
                                        <strong>Harga:</strong> {{ $preset['pricing'] }}
                                    </div>

                                    {{-- Models --}}
                                    <div style="margin-bottom:12px">
                                        <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:4px">Models</div>
                                        <div style="display:flex;flex-wrap:wrap;gap:4px">
                                            @foreach (array_slice($preset['models'], 0, 4) as $model)
                                                <code style="font-size:11px;background:#f1f5f9;color:#475569;padding:2px 6px;border-radius:4px;white-space:nowrap;max-width:180px;overflow:hidden;text-overflow:ellipsis">{{ $model }}</code>
                                            @endforeach
                                            @if (count($preset['models']) > 4)
                                                <code style="font-size:11px;background:#f1f5f9;color:#94a3b8;padding:2px 6px;border-radius:4px">+{{ count($preset['models']) - 4 }} more</code>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Recommended model --}}
                                    <div style="font-size:12px;color:#64748b;margin-bottom:14px">
                                        <span style="font-weight:500">Rekomendasi:</span>
                                        <code style="font-size:11px;background:#ede9fe;color:#6d28d9;padding:2px 6px;border-radius:4px">{{ $preset['recommended_model'] }}</code>
                                    </div>

                                    {{-- Action button --}}
                                    @if ($alreadyExists)
                                        <button type="button" disabled
                                                style="width:100%;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:not-allowed;background:#dcfce7;color:#15803d">
                                            Sudah Dikonfigurasi
                                        </button>
                                    @else
                                        <button type="button"
                                                wire:click="importPreset('{{ $preset['key'] }}')"
                                                style="width:100%;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;transition:opacity .2s"
                                                onmouseover="this.style.opacity='0.9'"
                                                onmouseout="this.style.opacity='1'">
                                            Gunakan Provider
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div style="margin-top:16px;padding:12px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;font-size:13px">
                            <strong style="color:#0284c7">Cara setup:</strong> Klik <strong>Gunakan Provider</strong> di atas, lalu buka <strong>Integrasi → Providers</strong> untuk mengisi API key dan mengaktifkan provider. Semua provider pakai format standard — ganti base_url + API key, model apa saja bisa.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <x-filament::button type="submit" color="primary">
                Save Settings
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
