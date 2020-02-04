/*
 * MIT License
 *
 * Copyright (c) 2020 DW Web-Engineering
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

import ReactDOM from "react-dom";
import React from "react";
import LayoutHeaderTasks from "./LayoutHeaderTasks";
import LayoutHeaderNotifications from "./LayoutHeaderNotifications";
import LayoutHeaderMessages from "./LayoutHeaderMessages";
import LayoutSidebarAside from "./LayoutSidebarAside";

let _layout_header_tasks_react_entry = document.getElementById('_layout_header_tasks_react_entry');

if (_layout_header_tasks_react_entry) {
    ReactDOM.render(<LayoutHeaderTasks/>, document.getElementById('_layout_header_tasks_react_entry'));
}

let _layout_header_notifications_react_entry = document.getElementById('_layout_header_notifications_react_entry');

if (_layout_header_notifications_react_entry) {
    ReactDOM.render(<LayoutHeaderNotifications/>, document.getElementById('_layout_header_notifications_react_entry'));
}

let _layout_header_messages_react_entry = document.getElementById('_layout_header_messages_react_entry');

if (_layout_header_messages_react_entry) {
    ReactDOM.render(<LayoutHeaderMessages/>, document.getElementById('_layout_header_messages_react_entry'));
}

let _layout_sidebar_aside_react_entry = document.getElementById('_layout_sidebar_aside_react_entry');

if (_layout_sidebar_aside_react_entry) {
    ReactDOM.render(<LayoutSidebarAside/>, document.getElementById('_layout_sidebar_aside_react_entry'));
}