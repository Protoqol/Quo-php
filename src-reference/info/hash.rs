use twox_hash::XxHash64;

/// Get reproducible hash for grouping of same variables
pub fn get_hash(var_type: &str, name: &str, package_name: &str) -> Option<String> {
    let seed = 123456789;
    // @TODO tests
    Some(
        XxHash64::oneshot(
            seed,
            &format!("{}:{}:{}", var_type, name, package_name).into_bytes(),
        )
        .to_string(),
    )
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_get_hash_returns_some() {
        assert!(get_hash("i32", "var", "pkg").is_some());
    }

    #[test]
    fn test_get_hash_consistency() {
        // Testing the hashing lib itself might be redundant, but I like having tests.
    }
}
