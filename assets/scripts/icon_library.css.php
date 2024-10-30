.amd-icon-library {
    position: fixed;
    top: 0;
    left: 0;
    background: rgba(0, 0, 0, .4);
    width: 100%;
    height: 100%;
    z-index: 999;
}
.amd-icon-library.--hidden {
    display: none;
}
.amd-icon-library > .--library {
    position: relative;
    background: var(--amd-wrapper-bg);
    width: 90%;
    max-width: 900px;
    max-height: calc(100vh - 200px);
    margin: 100px auto;
    padding: 8px;
    border-radius: 12px;
    overflow-y: auto;
    scrollbar-width: none;
}
.amd-icon-library > .--library::-webkit-scrollbar {
    display: none;
}
.amd-icon-library > .--library > .-icons,
.amd-icon-library > .--library > .-icons > .-icon {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
}
.amd-icon-library > .--library > .-icons > .-icon {
    flex: 0 0 80px;
    aspect-ratio: 1;
    background: var(--amd-primary-x-low);
    margin: 8px;
    border-radius: 10px;
    cursor: var(--amd-pointer);
}
.amd-icon-library > .--library > .-icons > .-icon.selected {
    background: var(--amd-primary);
    color: #fff;
}
.amd-icon-library > .--library > .-close {
    position: absolute;
    top: 0;
    left: 0;
    margin: 8px;
    cursor: var(--amd-pointer);
}

.amd-icon-picker,
.amd-icon-picker > .-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.amd-icon-picker {
    justify-content: space-between;
    background: var(--amd-primary);
    color: #fff;
    padding: 10px;
    border-radius: 12px;
    min-width: 80px;
    cursor: var(--amd-pointer);
    margin: 8px;
}
.amd-icon-picker > .-title {
    color: #fff;
    font-size: 15px;
    margin-inline-end: 12px;
}
.amd-icon-picker > .-icon {
    background: #fff;
    color: #414141;
    padding: 4px;
    border-radius: 6px;
}