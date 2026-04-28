#[cfg(test)]
mod lib_tests {
    use crate::{quo_create_payload, __private_quo_grouping_hash, QuoContext};

    #[cfg(target_family = "wasm")]
    use wasm_bindgen_test::*;

    #[cfg(target_family = "wasm")]
    wasm_bindgen_test_configure!(run_in_browser);

    #[test]
    fn test_payload() {
        let var = 42;
        let payload = quo_create_payload(&var, "var", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: false, package_name: "test", shared_grouping_hash: None });

        assert!(payload.meta.time_epoch_ms > 0);
        assert!(!payload.meta.uid.is_empty());
        assert_eq!(payload.meta.variable.name, "var");
        assert_eq!(payload.meta.variable.value, "42");
        assert!(!payload.meta.variable.is_expression);
    }

    #[cfg(target_family = "wasm")]
    #[wasm_bindgen_test]
    fn test_wasm_payload() {
        test_payload();
    }

    #[test]
    fn test_fn() {
        let fn_test_var = 1234;

        let payload =
            quo_create_payload(&fn_test_var, "fn_test_var", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: false, package_name: "test", shared_grouping_hash: None });
        assert_eq!(payload.meta.variable.name, "fn_test_var");
        assert_eq!(payload.meta.variable.value, "1234");
        assert_eq!(payload.meta.variable.var_type, "i32");
        assert!(!payload.meta.variable.is_mutable);
        assert!(!payload.meta.variable.is_constant);
        assert!(!payload.meta.variable.is_expression);
    }

    #[test]
    fn test_macro_const() {
        const VAR_I32: i32 = -1234;

        let payload = quo_create_payload(&VAR_I32, "VAR_I32", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: false, package_name: "test", shared_grouping_hash: None });
        assert_eq!(payload.meta.variable.name, "VAR_I32");
        assert_eq!(payload.meta.variable.value, "-1234");
        assert_eq!(payload.meta.variable.var_type, "i32");
        assert!(!payload.meta.variable.is_mutable);
        assert!(payload.meta.variable.is_constant);
        assert!(!payload.meta.variable.is_expression);
    }

    #[test]
    fn test_macro_i32() {
        let var_i32: i32 = -1234;

        let payload = quo_create_payload(&var_i32, "var_i32", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: false, package_name: "test", shared_grouping_hash: None });
        assert_eq!(payload.meta.variable.name, "var_i32");
        assert_eq!(payload.meta.variable.value, "-1234");
        assert_eq!(payload.meta.variable.var_type, "i32");
        assert!(!payload.meta.variable.is_mutable);
        assert!(!payload.meta.variable.is_constant);
        assert!(!payload.meta.variable.is_expression);
    }

    #[test]
    fn test_macro_u32() {
        let var_u32: u32 = 1234;

        let payload = quo_create_payload(&var_u32, "var_u32", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: false, package_name: "test", shared_grouping_hash: None });
        assert_eq!(payload.meta.variable.name, "var_u32");
        assert_eq!(payload.meta.variable.value, "1234");
        assert_eq!(payload.meta.variable.var_type, "u32");
        assert!(!payload.meta.variable.is_mutable);
        assert!(!payload.meta.variable.is_constant);
        assert!(!payload.meta.variable.is_expression);
    }

    #[test]
    fn test_macro_string() {
        let var_string: &str = "string";

        let payload = quo_create_payload(&var_string, "var_string", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: false, package_name: "test", shared_grouping_hash: None });
        assert_eq!(payload.meta.variable.name, "var_string");
        assert_eq!(payload.meta.variable.value, "\"string\"");
        assert_eq!(payload.meta.variable.var_type, "&str");
        assert!(!payload.meta.variable.is_mutable);
        assert!(!payload.meta.variable.is_constant);
        assert!(!payload.meta.variable.is_expression);
    }

    #[test]
    fn test_macro_bool() {
        let var_bool: bool = true;

        let payload = quo_create_payload(&var_bool, "var_bool", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: false, package_name: "test", shared_grouping_hash: None });
        assert_eq!(payload.meta.variable.name, "var_bool");
        assert_eq!(payload.meta.variable.value, "true");
        assert_eq!(payload.meta.variable.var_type, "bool");
        assert!(!payload.meta.variable.is_mutable);
        assert!(!payload.meta.variable.is_constant);
        assert!(!payload.meta.variable.is_expression);
    }

    #[test]
    fn test_macro_tuple() {
        let var_tuple: (bool, String, String, String, u32) = (
            false,
            String::from("hope"),
            String::from("this"),
            String::from("works"),
            23423,
        );

        let payload = quo_create_payload(&var_tuple, "var_tuple", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: false, package_name: "test", shared_grouping_hash: None });
        assert_eq!(payload.meta.variable.name, "var_tuple");
        assert_eq!(
            payload.meta.variable.value,
            "(false, \"hope\", \"this\", \"works\", 23423)"
        );
        assert_eq!(
            payload.meta.variable.var_type,
            "(bool, alloc::string::String, alloc::string::String, alloc::string::String, u32)"
        );
        assert!(!payload.meta.variable.is_mutable);
        assert!(!payload.meta.variable.is_constant);
        assert!(!payload.meta.variable.is_expression);
    }

    #[test]
    fn test_macro_array() {
        let var_array: [String; 3] = [
            String::from("hope"),
            String::from("this"),
            String::from("works"),
        ];

        let payload = quo_create_payload(&var_array, "var_array", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: false, package_name: "test", shared_grouping_hash: None });
        assert_eq!(payload.meta.variable.name, "var_array");
        assert_eq!(
            payload.meta.variable.value,
            "[\"hope\", \"this\", \"works\"]"
        );
        assert_eq!(payload.meta.variable.var_type, "[alloc::string::String; 3]");
        assert!(!payload.meta.variable.is_mutable);
        assert!(!payload.meta.variable.is_constant);
        assert!(!payload.meta.variable.is_expression);
    }

    #[test]
    fn test_macro_vec() {
        let var_vector: Vec<String> = vec![
            String::from("hope"),
            String::from("this"),
            String::from("works"),
        ];

        let payload = quo_create_payload(&var_vector, "var_vector", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: false, package_name: "test", shared_grouping_hash: None });
        assert_eq!(payload.meta.variable.name, "var_vector");
        assert_eq!(
            payload.meta.variable.value,
            "[\"hope\", \"this\", \"works\"]"
        );
        assert_eq!(
            payload.meta.variable.var_type,
            "alloc::vec::Vec<alloc::string::String>"
        );
        assert!(!payload.meta.variable.is_mutable);
        assert!(!payload.meta.variable.is_constant);
        assert!(!payload.meta.variable.is_expression);
    }

    #[test]
    fn test_macro_mut() {
        let mut var_mut = 1;
        let payload1 = quo_create_payload(&var_mut, "var_mut", QuoContext { line: line!(), file: file!(), is_mutable: true, is_expression: false, package_name: "test", shared_grouping_hash: None });
        assert_eq!(payload1.meta.variable.name, "var_mut");
        assert_eq!(payload1.meta.variable.value, "1");
        assert_eq!(payload1.meta.variable.var_type, "i32");
        assert!(payload1.meta.variable.is_mutable);
        assert!(!payload1.meta.variable.is_constant);
        assert!(!payload1.meta.variable.is_expression);

        var_mut = 2;
        let payload2 = quo_create_payload(&var_mut, "var_mut", QuoContext { line: line!(), file: file!(), is_mutable: true, is_expression: false, package_name: "test", shared_grouping_hash: None });
        assert_eq!(payload2.meta.variable.name, "var_mut");
        assert_eq!(payload2.meta.variable.value, "2");
        assert_eq!(payload2.meta.variable.var_type, "i32");
        assert!(payload2.meta.variable.is_mutable);
        assert!(!payload2.meta.variable.is_constant);
        assert!(!payload2.meta.variable.is_expression);
    }

    #[test]
    fn test_macro_immut() {
        let var_immut = 1;
        let payload = quo_create_payload(&var_immut, "var_immut", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: false, package_name: "test", shared_grouping_hash: None });
        assert_eq!(payload.meta.variable.name, "var_immut");
        assert_eq!(payload.meta.variable.value, "1");
        assert_eq!(payload.meta.variable.var_type, "i32");
        assert!(!payload.meta.variable.is_mutable);
        assert!(!payload.meta.variable.is_constant);
        assert!(!payload.meta.variable.is_expression);
    }

    #[test]
    #[allow(dead_code)]
    fn test_complex_type() {
        #[derive(Debug)]
        struct Complex {
            a: i32,
            b: String,
            c: Vec<i32>,
            d: bool,
        }

        let var_complex = Complex {
            a: 1,
            b: String::from("complex"),
            c: vec![1, 2, 3],
            d: true,
        };

        let payload =
            quo_create_payload(&var_complex, "var_complex", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: false, package_name: "test", shared_grouping_hash: None });
        assert_eq!(payload.meta.variable.name, "var_complex");
        assert_eq!(
            payload.meta.variable.var_type,
            "quo_rust::tests::lib_tests::test_complex_type::Complex"
        );
        assert!(payload.meta.variable.value.contains("a: 1"));
        assert!(payload.meta.variable.value.contains("complex"));
        assert!(!payload.meta.variable.is_mutable);
        assert!(!payload.meta.variable.is_constant);
        assert!(!payload.meta.variable.is_expression);
    }

    #[test]
    #[allow(dead_code)]
    fn test_large_var() {
        #[derive(Debug)]
        struct Large {
            a: i32,
            b: i32,
            c: i32,
            d: i32,
            e: i32,
            f: i32,
            g: i32,
            h: i32,
            i: i32,
            j: i32,
        }

        let var_large = Large {
            a: 1,
            b: 2,
            c: 3,
            d: 4,
            e: 5,
            f: 6,
            g: 7,
            h: 8,
            i: 9,
            j: 10,
        };

        let payload = quo_create_payload(&var_large, "var_large", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: false, package_name: "test", shared_grouping_hash: None });
        assert_eq!(payload.meta.variable.name, "var_large");
        assert_eq!(
            payload.meta.variable.var_type,
            "quo_rust::tests::lib_tests::test_large_var::Large"
        );
        assert!(payload.meta.variable.value.contains("j: 10"));
        assert!(!payload.meta.variable.is_mutable);
        assert!(!payload.meta.variable.is_constant);
        assert!(!payload.meta.variable.is_expression);
    }

    #[test]
    fn test_macro_mut_explicit() {
        #[allow(unused_mut)]
        let mut var_mut = 1;

        let payload = quo_create_payload(&var_mut, "var_mut", QuoContext { line: line!(), file: file!(), is_mutable: true, is_expression: false, package_name: "test", shared_grouping_hash: None });
        assert!(payload.meta.variable.is_mutable);
        assert!(!payload.meta.variable.is_expression);
    }

    #[test]
    fn test_macro_mut_binding() {
        let mut big_number = 170141183460469231731687303715884105727i128;
        
        // This is what quo!(big_number) does (pattern 2 in macros.rs)
        let payload = quo_create_payload(&big_number, "big_number", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: false, package_name: "test", shared_grouping_hash: None });
        
        // This will be FALSE because declarative macros cannot see if a binding is mutable.
        // T will be &i128 here or i128 depending on how it's called.
        // The macro currently does &big_number, which makes T = &i128.
        assert!(!payload.meta.variable.is_mutable);

        // However, if we pass it as an expression &mut big_number:
        // This is what quo!(&mut big_number) does (pattern 3 in macros.rs)
        let payload2 = quo_create_payload(&mut big_number, "&mut big_number", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: true, package_name: "test", shared_grouping_hash: None });
        
        // This should be TRUE because we detect &mut in the type name.
        assert!(payload2.meta.variable.is_mutable);
    }

    #[test]
    fn test_macro_mut_ref_auto() {
        let mut y = 1;
        let x = &mut y;

        // Automatically detected even if is_mutable parameter is false
        let payload = quo_create_payload(x, "x", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: false, package_name: "test", shared_grouping_hash: None });
        assert!(payload.meta.variable.is_mutable);
    }

    #[test]
    fn test_expression() {
        let payload = quo_create_payload(&(1 + 1), "1 + 1", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: true, package_name: "test", shared_grouping_hash: None });
        assert_eq!(payload.meta.variable.name, "1 + 1");
        assert_eq!(payload.meta.variable.value, "2");
        assert!(payload.meta.variable.is_expression);
    }

    // --- grouping_hash tests ---

    #[test]
    fn test_grouping_hash_is_some_without_shared() {
        // When no shared hash is provided (None), the payload should still compute its own hash.
        let var = 42;
        let payload = quo_create_payload(&var, "var", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: false, package_name: "test", shared_grouping_hash: None });
        assert!(payload.meta.variable.grouping_hash.is_some());
    }

    #[test]
    fn test_shared_grouping_hash_is_used() {
        // When a shared hash is provided, it should be used instead of the computed one.
        let shared_hash = Some("shared_hash_value".to_string());
        let var = 42;
        let payload = quo_create_payload(&var, "var", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: false, package_name: "test", shared_grouping_hash: shared_hash.clone() });
        assert_eq!(payload.meta.variable.grouping_hash, shared_hash);
    }

    #[test]
    fn test_multiple_args_share_grouping_hash() {
        // Simulates what quo!(43, "string", 54.6) does:
        // a single grouping hash is computed once and passed to every payload.
        let shared_hash = __private_quo_grouping_hash("43,\"string\",54.6,", "test");
        assert!(shared_hash.is_some(), "grouping hash must be computed");

        let a: i32 = 43;
        let b: &str = "string";
        let c: f64 = 54.6;

        let payload_a = quo_create_payload(&a, "a", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: false, package_name: "test", shared_grouping_hash: shared_hash.clone() });
        let payload_b = quo_create_payload(&b, "b", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: false, package_name: "test", shared_grouping_hash: shared_hash.clone() });
        let payload_c = quo_create_payload(&c, "c", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: false, package_name: "test", shared_grouping_hash: shared_hash.clone() });

        assert_eq!(
            payload_a.meta.variable.grouping_hash,
            payload_b.meta.variable.grouping_hash,
            "a and b must share the same grouping_hash"
        );
        assert_eq!(
            payload_b.meta.variable.grouping_hash,
            payload_c.meta.variable.grouping_hash,
            "b and c must share the same grouping_hash"
        );
        assert_eq!(
            payload_a.meta.variable.grouping_hash,
            shared_hash,
            "grouping_hash must equal the pre-computed shared hash"
        );
    }

    #[test]
    fn test_grouping_hash_differs_between_calls() {
        // Two separate quo!() calls — even with the same arguments — must produce different
        // grouping hashes because each invocation incorporates the current timestamp.
        let hash1 = __private_quo_grouping_hash("a,b,", "test");
        let hash2 = __private_quo_grouping_hash("a,b,", "test");
        assert_ne!(hash1, hash2, "same-arg calls at different times must produce different grouping hashes");

        // Different argument sets must also differ.
        let hash3 = __private_quo_grouping_hash("a,b,", "test");
        let hash4 = __private_quo_grouping_hash("x,y,z,", "test");
        assert_ne!(hash3, hash4, "different argument sets must produce different grouping hashes");
    }

    #[test]
    fn test_grouping_hash_unique_per_invocation() {
        // Each call to __private_quo_grouping_hash produces a unique hash so that
        // two separate quo!(...) macro calls never share a grouping_hash, even when
        // their arguments and package name are identical.
        let hashes: Vec<Option<String>> = (0..5)
            .map(|_| __private_quo_grouping_hash("var,other,", "test"))
            .collect();
        let unique: std::collections::HashSet<String> = hashes.into_iter().flatten().collect();
        assert_eq!(unique.len(), 5, "every invocation must produce a unique grouping hash");
    }
}
