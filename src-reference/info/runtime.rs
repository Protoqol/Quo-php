/// Get current runtime
pub fn get_runtime() -> Option<String> {
    Some(format!(
        "{}-{}",
        std::env::consts::OS,
        std::env::consts::ARCH
    ))
}

// Tests currently are pretty redundant.
#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_get_runtime_format() {
        let runtime = get_runtime().unwrap();
        let expected = format!("{}-{}", std::env::consts::OS, std::env::consts::ARCH);
        assert_eq!(runtime, expected);
    }

    #[test]
    fn test_get_runtime_not_empty() {
        let runtime = get_runtime();
        assert!(runtime.is_some());
        assert!(!runtime.unwrap().is_empty());
    }
}
