function closeToast() {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(-15px)';
    setTimeout(() => { 
        toast.remove()
    }, 200);
}

document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        closeToast();
    }, 4000);
})