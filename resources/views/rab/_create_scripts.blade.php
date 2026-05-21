<script>
let currentType = '';
let rowCount = 0;
const ic = 'w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none'; // input class
const nc = ic + ' text-right money-input'; // number input class
const sc = ic; // select class
const delBtn = `<button type="button" onclick="removeRow(this)" class="text-red-500 hover:text-red-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>`;

const expenseTypeSelect = document.getElementById('expenseTypeSelect');
const tableContainer = document.getElementById('expenseTableContainer');
const tableHead = document.getElementById('expenseTableHead');
const tableBody = document.getElementById('expenseTableBody');
const tableTitle = document.getElementById('expenseTableTitle');

const positionOptions = ['Direktur','Manajer','Admin','OB','Lainnya'].map(p => `<option value="${p}">${p}</option>`).join('');

const configs = {
    // operasional will be handled separately
    operasional: {
        title: 'Rincian Biaya Operasional',
        headers: [],
        row: () => '',
        calcRow: () => 0
    },
    petty_cash: {
        title: 'Rincian Petty Cash',
        headers: ['No','Nama Pengeluaran','Keterangan','Jumlah','Satuan','Harga Satuan','Admin','Total','Tanggal','Aksi'],
        row: (i) => `
            <td class="py-3 px-3 text-center text-sm text-gray-500">${i}</td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][expense_name]" required class="${ic}" placeholder="Nama pengeluaran"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][description]" class="${ic}" placeholder="Keterangan"></td>
            <td class="py-3 px-3"><input type="number" name="items[${i-1}][volume]" required step="0.01" min="0.01" value="1" class="w-20 ${nc} calc-trigger"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][unit]" required class="w-20 ${ic}" placeholder="pcs" value="pcs"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][unit_price]" required class="w-32 ${nc} calc-trigger money-input" placeholder="0" oninput="formatMoney(this)"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][admin_fee]" class="w-28 ${nc} calc-trigger money-input" placeholder="0" value="0" oninput="formatMoney(this)"></td>
            <td class="py-3 px-3 font-bold text-gray-800 row-total">Rp 0</td>
            <td class="py-3 px-3"><input type="date" name="items[${i-1}][transaction_date]" required class="${ic}"></td>
            <td class="py-3 px-3">${delBtn}</td>`,
        calcRow: (row) => {
            const vol = parseFloat(row.querySelector('[name*="volume"]')?.value) || 0;
            const price = parseMoney(row.querySelector('[name*="unit_price"]')?.value);
            const admin = parseMoney(row.querySelector('[name*="admin_fee"]')?.value);
            return (vol * price) + admin;
        }
    },
    gaji: {
        title: 'Rincian Biaya Gaji',
        headers: ['No','Nama','Jabatan','No. Rek','Hadir (hari)','Gaji Pokok','Uang Makan/Hari','Transport/Hari','Lembur','Total Gaji','Catatan','Aksi'],
        row: (i) => `
            <td class="py-3 px-3 text-center text-sm text-gray-500">${i}</td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][employee_name]" required class="${ic}" placeholder="Nama"></td>
            <td class="py-3 px-3"><select name="items[${i-1}][position]" required class="${sc}"><option value="">-- Pilih --</option>${positionOptions}</select></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][bank_account_number]" required class="w-28 ${ic}" placeholder="No. Rek"></td>
            <td class="py-3 px-3"><input type="number" name="items[${i-1}][attendance_days]" required min="0" class="w-16 ${nc} calc-trigger" placeholder="0"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][base_salary]" required class="w-32 ${nc} calc-trigger money-input" placeholder="0" oninput="formatMoney(this)"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][meal_allowance_daily]" class="w-28 ${nc} calc-trigger money-input" placeholder="0" value="0" oninput="formatMoney(this)"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][transport_daily]" class="w-28 ${nc} calc-trigger money-input" placeholder="20.000" value="20.000" oninput="formatMoney(this)"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][overtime]" class="w-28 ${nc} calc-trigger money-input" placeholder="0" value="0" oninput="formatMoney(this)"></td>
            <td class="py-3 px-3 font-bold text-gray-800 row-total">Rp 0</td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][notes]" class="${ic}" placeholder="Catatan"></td>
            <td class="py-3 px-3">${delBtn}</td>`,
        calcRow: (row) => {
            const days = parseInt(row.querySelector('[name*="attendance_days"]')?.value) || 0;
            const base = parseMoney(row.querySelector('[name*="base_salary"]')?.value);
            const meal = parseMoney(row.querySelector('[name*="meal_allowance_daily"]')?.value);
            const transport = parseMoney(row.querySelector('[name*="transport_daily"]')?.value);
            const overtime = parseMoney(row.querySelector('[name*="overtime"]')?.value);
            return base + (days * meal) + (days * transport) + overtime;
        }
    },
    bulanan: {
        title: 'Rincian Biaya Bulanan',
        headers: ['No','Keterangan','No.Regist/ID','A/N','Total Pengeluaran','Biaya Admin','Subtotal','Tanggal','Aksi'],
        row: (i) => `
            <td class="py-3 px-3 text-center text-sm text-gray-500">${i}</td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][payment_name]" required class="${ic}" placeholder="Keterangan"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][registration_number]" class="w-28 ${ic}" placeholder="No. Regist/ID"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][account_name]" class="w-28 ${ic}" placeholder="A/N"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][total_expense]" required class="w-36 ${nc} calc-trigger money-input" placeholder="0" oninput="formatMoney(this)"></td>
            <td class="py-3 px-3"><input type="text" name="items[${i-1}][admin_fee]" class="w-28 ${nc} calc-trigger money-input" placeholder="0" value="0" oninput="formatMoney(this)"></td>
            <td class="py-3 px-3 font-bold text-gray-800 row-total">Rp 0</td>
            <td class="py-3 px-3"><input type="date" name="items[${i-1}][transaction_date]" required class="${ic}"></td>
            <td class="py-3 px-3">${delBtn}</td>`,
        calcRow: (row) => {
            const expense = parseMoney(row.querySelector('[name*="total_expense"]')?.value);
            const admin = parseMoney(row.querySelector('[name*="admin_fee"]')?.value);
            return expense + admin;
        }
    }
};

// ── Money formatting helpers ──
function formatMoney(el) {
    let val = el.value.replace(/[^0-9]/g, '');
    if (val === '') { el.value = ''; return; }
    el.value = Number(val).toLocaleString('id-ID');
}
function parseMoney(val) {
    if (!val) return 0;
    return parseFloat(String(val).replace(/\./g, '').replace(/,/g, '.')) || 0;
}

// Before form submit: convert formatted money back to raw numbers
document.getElementById('rabForm')?.addEventListener('submit', function() {
    document.querySelectorAll('.money-input').forEach(input => {
        input.value = parseMoney(input.value);
    });
});

const operationalGroups = [
    "Honor Pencari Peserta",
    "Uang Transport / Honor Peserta Uji Serkom",
    "Operasional Pembekalan",
    "Operasional Uji Serkom",
    "Honor Asesor"
];

expenseTypeSelect?.addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const code = selected.dataset.code;
    
    document.getElementById('expenseTableContainer').style.display = 'none';
    document.getElementById('operationalTableContainer').style.display = 'none';
    
    if (!code) return;

    currentType = code;
    
    if (code === 'operasional') {
        document.getElementById('operationalTableContainer').style.display = 'block';
        renderOperationalGroups();
    } else if (configs[code]) {
        rowCount = 0;
        const cfg = configs[code];
        tableTitle.textContent = cfg.title;

        const table = document.getElementById('expenseTable');
        if (table) {
            table.classList.remove('min-w-[900px]', 'min-w-[1000px]', 'min-w-[1200px]');
            if (code === 'petty_cash') {
                table.classList.add('min-w-[1000px]');
            } else if (code === 'gaji') {
                table.classList.add('min-w-[1200px]');
            } else if (code === 'bulanan') {
                table.classList.add('min-w-[900px]');
            }
        }

        tableHead.innerHTML = '<tr class="text-xs text-gray-500 border-b border-gray-200 bg-gray-50">' +
            cfg.headers.map(h => `<th class="py-3 px-3 font-bold whitespace-nowrap">${h}</th>`).join('') + '</tr>';
        tableBody.innerHTML = '';
        document.getElementById('expenseTableContainer').style.display = 'block';

        addRow();
    }
});

function addRow() {
    if (!currentType || currentType === 'operasional') return;
    rowCount++;
    const tr = document.createElement('tr');
    tr.className = 'border-b border-gray-50 hover:bg-blue-50/30 transition-colors';
    tr.innerHTML = configs[currentType].row(rowCount);
    tableBody.appendChild(tr);

    tr.querySelectorAll('.calc-trigger').forEach(input => {
        input.addEventListener('input', () => calculateRow(tr));
    });
}

function removeRow(btn) {
    const row = btn.closest('tr');
    row.remove();
    reindexRows();
    calculateGrandTotal();
}

function reindexRows() {
    const rows = tableBody.querySelectorAll('tr');
    rows.forEach((row, idx) => {
        row.querySelector('td:first-child').textContent = idx + 1;
        row.querySelectorAll('input, select').forEach(input => {
            const name = input.name.replace(/items\[\d+\]/, `items[${idx}]`);
            input.name = name;
        });
    });
    rowCount = rows.length;
}

function calculateRow(row) {
    const total = configs[currentType].calcRow(row);
    const totalCell = row.querySelector('.row-total');
    if (totalCell) totalCell.textContent = 'Rp ' + total.toLocaleString('id-ID');
    calculateGrandTotal();
}

function calculateGrandTotal() {
    let grand = 0;
    tableBody.querySelectorAll('tr').forEach(row => {
        grand += configs[currentType].calcRow(row);
    });
    document.getElementById('grandTotal').textContent = 'Rp ' + grand.toLocaleString('id-ID');
}

// ==========================================
// OPERASIONAL FUNCTIONS
// ==========================================
function renderOperationalGroups() {
    const wrapper = document.getElementById('operationalGroupsWrapper');
    wrapper.innerHTML = '';
    
    operationalGroups.forEach((groupName, gIdx) => {
        const groupHtml = `
            <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm bg-white" data-group-index="${gIdx}">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                    <h4 class="font-bold text-gray-800 text-sm flex items-center">
                        <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mr-2 text-xs">${gIdx + 1}</span>
                        ${groupName}
                        <input type="hidden" name="op_groups[${gIdx}][name]" value="${groupName}">
                    </h4>
                    <button type="button" onclick="addOperationalRow(${gIdx})" class="text-emerald-600 hover:text-emerald-800 text-xs font-bold flex items-center bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Tambah Item
                    </button>
                </div>
                <div class="overflow-x-hidden md:overflow-x-auto p-3 md:p-0 bg-slate-50 md:bg-white">
                    <table class="w-full text-left op-table block md:table">
                        <thead class="hidden md:table-header-group bg-white border-b border-gray-100 text-xs text-gray-500">
                            <tr>
                                <th class="py-2 px-3 w-10 text-center">No</th>
                                <th class="py-2 px-3">Item</th>
                                <th class="py-2 px-3 w-24">Volume</th>
                                <th class="py-2 px-3 w-20">Satuan</th>
                                <th class="py-2 px-3 w-36">Rp/Unit</th>
                                <th class="py-2 px-3 w-32 text-right">Jumlah</th>
                                <th class="py-2 px-3">Keterangan</th>
                                <th class="py-2 px-3 w-12 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="opBody_${gIdx}" class="block md:table-row-group">
                        </tbody>
                        <tfoot class="block md:table-footer-group bg-white md:bg-gray-50/50 mt-2 md:mt-0 rounded-xl md:rounded-none overflow-hidden shadow-sm md:shadow-none border border-gray-100 md:border-0">
                            <tr class="block md:table-row flex justify-between items-center p-4 md:p-0">
                                <td colspan="5" class="hidden md:table-cell py-3 px-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Subtotal:</td>
                                <td class="block md:hidden text-sm font-bold text-gray-500 uppercase">Subtotal</td>
                                <td class="block md:table-cell py-0 md:py-3 px-0 md:px-3 text-right font-extrabold text-blue-600 op-subtotal text-lg md:text-base">Rp 0</td>
                                <td colspan="2" class="hidden md:table-cell"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        `;
        wrapper.insertAdjacentHTML('beforeend', groupHtml);
        
        // Add one default row per group
        addOperationalRow(gIdx);
    });
}

function addOperationalRow(gIdx) {
    const tbody = document.getElementById(`opBody_${gIdx}`);
    const rIdx = tbody.children.length; // row index inside this group
    
    const tr = document.createElement('tr');
    tr.className = 'block md:table-row border border-gray-100 md:border-b md:border-x-0 md:border-t-0 border-gray-50 hover:bg-slate-50 transition-colors op-row rounded-xl md:rounded-none bg-white p-4 md:p-0 mb-4 md:mb-0 shadow-sm md:shadow-none';
    tr.innerHTML = `
        <td class="hidden md:table-cell py-2 px-3 text-center text-xs text-gray-400 op-row-num">${rIdx + 1}</td>
        <td class="block md:table-cell py-2 md:py-2 px-0 md:px-3">
            <div class="md:hidden flex justify-between items-center mb-3">
                <span class="text-sm font-bold text-gray-700">Item <span class="op-row-num-mobile">${rIdx + 1}</span></span>
                <button type="button" onclick="removeOperationalRow(this, ${gIdx})" class="text-red-500 bg-red-50 hover:bg-red-100 p-1.5 rounded-md transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
            <label class="md:hidden block text-[10px] font-bold text-gray-500 uppercase mb-1">Nama Item</label>
            <input type="text" name="op_groups[${gIdx}][items][${rIdx}][item_name]" required class="${ic}" placeholder="Nama item">
        </td>
        <td class="inline-block md:table-cell w-[48%] md:w-auto py-2 md:py-2 px-0 md:px-3 pr-1 md:pr-3">
            <label class="md:hidden block text-[10px] font-bold text-gray-500 uppercase mb-1">Volume</label>
            <input type="number" name="op_groups[${gIdx}][items][${rIdx}][volume]" required step="0.01" min="0.01" class="w-full ${nc} op-calc" placeholder="0">
        </td>
        <td class="inline-block md:table-cell w-[48%] md:w-auto py-2 md:py-2 px-0 md:px-3 pl-1 md:pl-3">
            <label class="md:hidden block text-[10px] font-bold text-gray-500 uppercase mb-1">Satuan</label>
            <input type="text" name="op_groups[${gIdx}][items][${rIdx}][unit]" required class="w-full ${ic}" placeholder="pcs">
        </td>
        <td class="block md:table-cell py-2 md:py-2 px-0 md:px-3">
            <label class="md:hidden block text-[10px] font-bold text-gray-500 uppercase mb-1">Rp / Unit</label>
            <input type="text" name="op_groups[${gIdx}][items][${rIdx}][unit_price]" required class="w-full ${nc} money-input op-calc" placeholder="0" oninput="formatMoney(this)">
        </td>
        <td class="block md:table-cell py-3 md:py-2 px-4 md:px-3 mt-2 md:mt-0 bg-gray-50 md:bg-transparent rounded-lg md:rounded-none">
            <div class="flex justify-between md:justify-end items-center md:block">
                <span class="md:hidden text-xs font-bold text-gray-500 uppercase">Jumlah</span>
                <span class="font-bold text-gray-800 op-row-total text-sm md:text-sm md:text-right block">Rp 0</span>
            </div>
        </td>
        <td class="block md:table-cell py-2 md:py-2 px-0 md:px-3 mt-2 md:mt-0">
            <label class="md:hidden block text-[10px] font-bold text-gray-500 uppercase mb-1">Keterangan</label>
            <input type="text" name="op_groups[${gIdx}][items][${rIdx}][note]" class="w-full ${ic}" placeholder="Ket">
        </td>
        <td class="hidden md:table-cell py-2 px-3 text-center">
            <button type="button" onclick="removeOperationalRow(this, ${gIdx})" class="text-red-400 hover:text-red-600 transition p-1.5 rounded hover:bg-red-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </td>
    `;
    
    tbody.appendChild(tr);
    
    tr.querySelectorAll('.op-calc').forEach(input => {
        input.addEventListener('input', () => calcOperationalGroup(gIdx));
    });
}

function removeOperationalRow(btn, gIdx) {
    const tbody = document.getElementById(`opBody_${gIdx}`);
    if (tbody.children.length <= 1) {
        alert('Minimal harus ada 1 item pada setiap kelompok!');
        return;
    }
    
    const row = btn.closest('tr');
    row.remove();
    
    // Reindex
    Array.from(tbody.children).forEach((tr, index) => {
        tr.querySelector('.op-row-num').textContent = index + 1;
        const mobileNum = tr.querySelector('.op-row-num-mobile');
        if (mobileNum) mobileNum.textContent = index + 1;
        
        tr.querySelectorAll('input').forEach(input => {
            const name = input.name.replace(/\[items\]\[\d+\]/, `[items][${index}]`);
            input.name = name;
        });
    });
    
    calcOperationalGroup(gIdx);
}

function calcOperationalGroup(gIdx) {
    const tbody = document.getElementById(`opBody_${gIdx}`);
    let groupTotal = 0;
    
    Array.from(tbody.children).forEach(tr => {
        const vol = parseFloat(tr.querySelector('[name*="[volume]"]')?.value) || 0;
        const price = parseMoney(tr.querySelector('[name*="[unit_price]"]')?.value);
        const rowTotal = vol * price;
        
        tr.querySelector('.op-row-total').textContent = 'Rp ' + rowTotal.toLocaleString('id-ID');
        groupTotal += rowTotal;
    });
    
    const container = tbody.closest('[data-group-index]');
    container.querySelector('.op-subtotal').textContent = 'Rp ' + groupTotal.toLocaleString('id-ID');
    
    calcOperationalGrandTotal();
}

function calcOperationalGrandTotal() {
    let grand = 0;
    document.querySelectorAll('#operationalGroupsWrapper .op-subtotal').forEach(el => {
        grand += parseMoney(el.textContent.replace('Rp ', ''));
    });
    document.getElementById('operationalGrandTotal').textContent = 'Rp ' + grand.toLocaleString('id-ID');
}

// Modal open/close logic
function openCreateRabModal() {
    const modal = document.getElementById('createRabModal');
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeCreateRabModal() {
    const modal = document.getElementById('createRabModal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

// Allow escape key to close this modal
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeCreateRabModal();
    }
});
</script>
