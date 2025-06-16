<?php
require('system/dbconfig.php');
require('system/head.php');
?>
<main class="max-w-7xl mx-auto px-4 py-10">
    <h1 class="text-3xl tracking-wider text-[#27f2f2] font-bold uppercase text-center mb-10 drop-shadow-[0_0_10px_#27f2f282] " 
        data-aos="fade-up" data-aos-duration="800">
        <div class="inline-block border-b-4 border-[#27f2f2] pb-4">
            DANH MỤC SẢN PHẨM
        </div>
    </h1>
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-6" data-aos="fade-up" data-aos-duration="1400">
        <?php
        $sql = "
        SELECT p.id, p.name, 
            GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') AS categories,
            GROUP_CONCAT(DISTINCT CONCAT(c.name, ':', IFNULL(sf.subfolder_data, '')) SEPARATOR '|') AS subfolder_data
        FROM post p
        LEFT JOIN category c ON c.post = p.name
        LEFT JOIN (
            SELECT post, category, GROUP_CONCAT(CONCAT(id, '-', name) SEPARATOR ', ') AS subfolder_data
            FROM subfolder
            GROUP BY post, category
        ) sf ON sf.post = p.name AND sf.category = c.name
        GROUP BY p.id, p.name
        ";

        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '
                    <div onclick="handleClick(this)" 
                        class="rounded-2xl overflow-hidden border-2 border-gray-700 bg-gray-900 p-0 flex flex-col items-center hover:scale-105 hover:border-gray-600 hover:bg-gray-800 transform transition-transform duration-200"
                        data-id="' . htmlspecialchars($row['id']) . '" 
                        data-name="' . htmlspecialchars($row['name']) . '" 
                        data-thumbnail="assets/upload/' . htmlspecialchars($row['id']) . '/thumbnail.png" 
                        data-category="' . htmlspecialchars($row['categories']) . '"
                        data-subfolders="' . htmlspecialchars($row['subfolder_data']) . '">
                        <img src="assets/upload/' . htmlspecialchars($row['id']) . '/thumbnail.png"  alt="' . htmlspecialchars($row['name']) . '"
                            class="w-full h-full object-cover bg-gray-700 rounded-2xl rounded-b-none bg-[url(\'assets/img/background.avif\')] bg-cover bg-center">
                        <h2 class="text-xl text-[#27f2f2] text-center drop-shadow-[0_0_5px_#27f2f282] font-semibold mt-2 mb-2">' . htmlspecialchars($row['name']) . '</h2>
                    </div>          
                ';
            }
        } else {
        echo '<p class="text-red-500">Không có sản phẩm nào được tìm thấy.</p>';
        }
        ?>
    </div> 
</main>

<div id="modalPost" class="p-8 fixed inset-0 bg-black bg-opacity-85 flex justify-center items-center hidden z-50">
    <div id="modalContent" class="bg-gray-900 rounded-2xl border-2 border-[#27f2f2] bg-gray-800 p-0 w-full max-w-md shadow-lg relative overflow-hidden">
        <button id="closeModal" class="absolute top-3 right-3 text-white text-2xl transform transition-transform duration-300 hover:rotate-180 z-20">
            <i class="fa fa-close"></i>
        </button>
        <img id="modalThumbnail" src="" alt="Ảnh xe" class="w-full h-full object-cover bg-gray-700 rounded-2xl rounded-b-none bg-[url('assets/img/background.avif')] bg-cover bg-center">
        <h2 id="modalName" class="text-2xl font-bold text-[#27f2f2] drop-shadow-[0_0_5px_#27f2f282] mt-4 mb-4 text-center"></h2>
        <div id="modalCategory" class="px-4 text-center mb-3"></div>
    </div>
</div>


<script>
function handleClick(element) {
    const id = element.getAttribute('data-id');
    const name = element.getAttribute('data-name');
    const thumbnail = element.getAttribute('data-thumbnail');
    const categorys = element.getAttribute('data-category').split(',');
    const subfolderData = element.getAttribute('data-subfolders').split('|');

    document.getElementById('modalThumbnail').src = thumbnail;
    document.getElementById('modalName').textContent = name;

    const modalCategory = document.getElementById('modalCategory');
    modalCategory.innerHTML = '';

    let currentView = 'categories';
    let selectedCategory = null;

    function showCategories() {
        modalCategory.innerHTML = '';
        currentView = 'categories';

        categorys.forEach((category, index) => {
            const categoryName = category.trim();
            const [cat, subfolders] = subfolderData[index]?.split(':') || [categoryName, ''];
            const categoryContainer = document.createElement('div');
            categoryContainer.className = 'w-full text-center mb-3';
            
            if (categoryName !== "") {
                const categoryButton = document.createElement('button');
                categoryButton.textContent = `${categoryName}`;
                if (categorys.length === 1) {
                    modalCategory.classList.add('flex', 'justify-center');
                    modalCategory.classList.remove('grid', 'grid-cols-2', 'gap-2');
                } else {
                    modalCategory.classList.add('grid', 'grid-cols-2', 'gap-2');
                    modalCategory.classList.remove('flex', 'justify-center');
                }
                categoryButton.className = 'text-[#27f2f2] text-shadow-[0_0_5px_#27f2f282] font-bold bg-gray-600 px-4 py-2 rounded-lg hover:scale-105 hover:bg-gray-700 transform transition-transform duration-200';                    

                categoryButton.onclick = () => {
                    window.location.href = `view.php?cate=${name}&sub=${categoryName}`;
                    // if (subfolders) {
                    //     modalCategory.classList.add('flex', 'justify-center');
                    //     modalCategory.classList.remove('grid', 'grid-cols-2', 'gap-2');                
                    //     selectedCategory = { 
                    //         name: categoryName, 
                    //         subfolders: subfolders.split(', ').filter(Boolean)
                    //     };
                    //     showSubfolders();
                    // } else {
                    //     Swal.fire({
                    //         icon: 'error',
                    //         title: 'Lỗi!',
                    //         text: 'Quản trị viên chưa cập nhật phân loại con!',
                    //         timer: 1500,
                    //         showConfirmButton: false,
                    //         position: 'top-end',
                    //         toast: true
                    //     });
                    // }
                };
                categoryContainer.appendChild(categoryButton);
            }

            modalCategory.appendChild(categoryContainer);
        });
    }

    // function showSubfolders() {
    //     modalCategory.innerHTML = '';
    //     currentView = 'subfolders';

    //     const subfolderContainer = document.createElement('div');
    //     subfolderContainer.className = 'px-4 text-center mb-3';

    //     const categoryTitle = document.createElement('h3');
    //     categoryTitle.innerHTML = `<p class="mb-4 text-[#27f2f2] font-bold">${selectedCategory.name}</p>`;
    //     subfolderContainer.appendChild(categoryTitle);

    //     const subfolderWrapper = document.createElement('div');
    //     subfolderWrapper.className = 'grid grid-cols-2 gap-2';

    //     selectedCategory.subfolders.forEach(subfolder => {
    //         if (selectedCategory.subfolders.length === 1) {
    //             subfolderWrapper.classList.add('flex', 'justify-center');
    //             subfolderWrapper.classList.remove('grid', 'grid-cols-2', 'gap-2');
    //         } else {
    //             subfolderWrapper.classList.add('grid', 'grid-cols-2', 'gap-2');
    //             subfolderWrapper.classList.remove('flex', 'justify-center');
    //         }            
    //         const [subfolderId, subfolderName] = subfolder.split('-');
    //         const btn = document.createElement('button');
    //         btn.textContent = subfolderName.trim();
    //         btn.className = 'text-[#27f2f2] text-shadow-[0_0_5px_#27f2f282] font-bold bg-gray-600 px-4 py-2 rounded-lg hover:scale-105 hover:bg-gray-700 transform transition-transform duration-200';
    //         // btn.onclick = () => window.location.href = `view.php?id=${id}&sub=${subfolderId}`;
    //         btn.onclick = () => window.location.href = `view.php?cate=${name}&sub=${selectedCategory.name}&subfolder=${subfolderName}`;
    //         subfolderWrapper.appendChild(btn);
    //     });

    //     subfolderContainer.appendChild(subfolderWrapper);

    //     const backButton = document.createElement('button');
    //     backButton.innerHTML = '<i class="fa fa-arrow-left mr-2"></i> Quay lại';
    //     backButton.className = 'text-white font-bold bg-red-500 px-4 py-2 rounded-lg mt-4 hover:scale-105 transform transition-transform duration-500';
    //     backButton.onclick = () => showCategories();
    //     subfolderContainer.appendChild(backButton);

    //     modalCategory.appendChild(subfolderContainer);
    // }

    showCategories();

    const modal = document.getElementById('modalPost');
    const modalContent = document.getElementById('modalContent');
    modal.classList.remove('hidden');
    setTimeout(() => modalContent.classList.remove('translate-y-[-100%]', 'opacity-0'), 10);
}

document.getElementById('closeModal').addEventListener('click', () => closeModal());
window.addEventListener('click', (e) => {
    if (e.target.id === 'modalPost') closeModal();
});

function closeModal() {
    const modal = document.getElementById('modalPost');
    const modalContent = document.getElementById('modalContent');
    modalContent.classList.add('translate-y-[-100%]', 'opacity-0');
    setTimeout(() => modal.classList.add('hidden'), 500);
}
</script>

<?php
require('system/foot.php');
?>
