const fs = require('fs');
const pdf = require('pdf-parse');

let dataBuffer = fs.readFileSync('LAPORAN KEUANGAN PT SBK BULAN FEBRUARI 2026.pdf');

pdf(dataBuffer).then(function(data) {
    console.log(data.text);
}).catch(function(error) {
    console.error(error);
});
