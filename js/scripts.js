// Copiar CBU
function copiarCBU() {
    const cbu = document.getElementById("cbu");
    navigator.clipboard.writeText(cbu.innerText).then(() => {
        alert("CBU copiado!");
    }).catch(err => {
        console.error('Error al copiar el CBU: ', err);
    });
}
