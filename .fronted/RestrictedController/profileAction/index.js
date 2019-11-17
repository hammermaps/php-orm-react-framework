// Copyright 2019. DW </> Web-Engineering. All rights reserved.
// PLEASE DO NOT EDIT THIS FILE! PLEASE DO NOT EDIT THIS FILE!  PLEASE DO NOT EDIT THIS FILE!  PLEASE DO NOT EDIT THIS FILE!

import React from 'react'
import ReactDOM from 'react-dom';
import Routes from "./routes";
import {HashRouter, withRouter} from "react-router-dom";

const initProps = window.INIT_PROPS;
const renderDOM = initProps.reactDOM;

const App = ({ ...props }) => (
    <HashRouter>
        <Routes {...props} />
    </HashRouter>
);

ReactDOM.render(<App {...initProps} />, document.getElementById(renderDOM));

// Copyright 2019. DW </> Web-Engineering. All rights reserved.
// PLEASE DO NOT EDIT THIS FILE! PLEASE DO NOT EDIT THIS FILE!  PLEASE DO NOT EDIT THIS FILE!  PLEASE DO NOT EDIT THIS FILE!