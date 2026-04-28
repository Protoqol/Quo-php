/// This macro sends the provided variable(s) or expression(s) to Quo using the `__private_quo` function.
/// It is only active when `debug_assertions` are enabled (typically in debug builds).
///
/// > Note that provided variables need to implement Debug.
///
/// # Examples
///
/// ### Sending a single variable
/// ```rust
/// # extern crate quo_rust as quo;
/// use quo::quo;
///
/// let variable = 12;
/// quo!(variable);
/// ```
///
/// ### Sending a mutable variable
/// To report mutability for a variable binding, use the `mut` keyword:
/// ```rust
/// # extern crate quo_rust as quo;
/// use quo::quo;
///
/// let mut big_number = 170141183460469231731687303715884105727i128;
/// quo!(mut big_number);
/// ```
///
/// Alternatively, passing a mutable reference also works automatically:
/// ```rust
/// # extern crate quo_rust as quo;
/// use quo::quo;
///
/// let mut big_number = 170141183460469231731687303715884105727i128;
/// quo!(&mut big_number);
/// ```
///
/// ### Sending an expression
/// ```rust
/// # extern crate quo_rust as quo;
/// use quo::quo;
///
/// quo!(1 + 1);
/// ```
///
/// ### Sending multiple arguments
/// ```rust
/// # extern crate quo_rust as quo;
/// use quo::quo;
///
/// let variable = 43;
/// quo!(variable, "string", 1 + 1);
/// ```
#[macro_export]
macro_rules! quo {
    // Mutable var(s)
    ($( mut $var:ident ), + $(,)?) => {{
        #[cfg(debug_assertions)]
        {
            let quo_package_name = option_env!("CARGO_PKG_NAME").unwrap_or("Rust project"); // Make sure we don't just get `quo-rust`.
            let __quo_grouping_hash = $crate::__private_quo_grouping_hash(
                concat!($(stringify!($var), ","),+),
                quo_package_name,
            );

            $(
                #[cfg(target_family = "wasm")]
                {
                    let quo_file_path = concat!(env!("CARGO_MANIFEST_DIR"), "/", file!());

                    $crate::__private_quo(&$var, stringify!($var), $crate::__private_QuoContext {
                        line: line!(),
                        file: &quo_file_path,
                        is_mutable: true,
                        is_expression: false,
                        package_name: quo_package_name,
                        shared_grouping_hash: __quo_grouping_hash.clone(),
                    });
                }

                #[cfg(not(target_family = "wasm"))]
                {
                    let quo_path_buf = std::path::Path::new(env!("CARGO_MANIFEST_DIR")).join(file!());
                    let quo_file_path = quo_path_buf.to_str().unwrap_or(file!());

                    $crate::__private_quo(&$var, stringify!($var), $crate::__private_QuoContext {
                        line: line!(),
                        file: quo_file_path,
                        is_mutable: true,
                        is_expression: false,
                        package_name: quo_package_name,
                        shared_grouping_hash: __quo_grouping_hash.clone(),
                    });
                }
            )*
        }
    }};

    // Var(s)
    ($( $var:ident ), + $(,)?) => {
        #[cfg(debug_assertions)]
        {
            let __quo_package_name = option_env!("CARGO_PKG_NAME").unwrap_or("Rust project"); // Make sure we don't just get `quo-rust`.
            let __quo_grouping_hash = $crate::__private_quo_grouping_hash(
                concat!($(stringify!($var), ","),+),
                __quo_package_name,
            );

            $(
                {
                    #[cfg(target_family = "wasm")]
                    {
                        let quo_file_path = concat!(env!("CARGO_MANIFEST_DIR"), "/", file!());

                        $crate::__private_quo(&$var, stringify!($var), $crate::__private_QuoContext {
                            line: line!(),
                            file: &quo_file_path.to_owned(),
                            is_mutable: false,
                            is_expression: false,
                            package_name: __quo_package_name,
                            shared_grouping_hash: __quo_grouping_hash.clone(),
                        });
                    }

                    #[cfg(not(target_family = "wasm"))]
                    {
                        let quo_path_buf = std::path::Path::new(env!("CARGO_MANIFEST_DIR")).join(file!());
                        let quo_file_path = quo_path_buf.to_str().unwrap_or(file!());

                        $crate::__private_quo(&$var, stringify!($var), $crate::__private_QuoContext {
                            line: line!(),
                            file: quo_file_path,
                            is_mutable: false,
                            is_expression: false,
                            package_name: __quo_package_name,
                            shared_grouping_hash: __quo_grouping_hash.clone(),
                        });
                    }
                }
            )*
        }
    };

    // Expression(s)
    ($( $var:expr ), + $(,)?) => {
        #[cfg(debug_assertions)]
        {
            let __quo_package_name = option_env!("CARGO_PKG_NAME").unwrap_or("Rust project"); // Make sure we don't just get `quo-rust`.
            let __quo_grouping_hash = $crate::__private_quo_grouping_hash(
                concat!($(stringify!($var), ","),+),
                __quo_package_name,
            );

            $(
                {
                    #[cfg(target_family = "wasm")]
                    {
                        let quo_file_path = concat!(env!("CARGO_MANIFEST_DIR"), "/", file!());

                        $crate::__private_quo(&$var, stringify!($var), $crate::__private_QuoContext {
                            line: line!(),
                            file: &quo_file_path.to_owned(),
                            is_mutable: false,
                            is_expression: true,
                            package_name: __quo_package_name,
                            shared_grouping_hash: __quo_grouping_hash.clone(),
                        });
                    }

                    #[cfg(not(target_family = "wasm"))]
                    {
                        let quo_path_buf = std::path::Path::new(env!("CARGO_MANIFEST_DIR")).join(file!());
                        let quo_file_path = quo_path_buf.to_str().unwrap_or(file!());

                        $crate::__private_quo(&$var, stringify!($var), $crate::__private_QuoContext {
                            line: line!(),
                            file: quo_file_path,
                            is_mutable: false,
                            is_expression: true,
                            package_name: __quo_package_name,
                            shared_grouping_hash: __quo_grouping_hash.clone(),
                        });
                    }
                }
            )*
        }
    };
}
